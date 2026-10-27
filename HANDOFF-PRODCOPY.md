# Production-data rehearsal (prod copy DB)

Read this before re-testing migrations against real data.

## What exists now

| thing | value |
|---|---|
| dump | `~/Downloads/u241077453_school_app_10_2026.sql` (4.0 MB, phpMyAdmin 5.2.2, **MariaDB 11.8.9**, generated Oct 21 2026 06:54) |
| source DB name in dump | `u241077453_school_app` |
| local server | private mysqld **8.0.36** in `/tmp/prodtest`, socket `/tmp/prodtest/prodtest.sock`, port **3307**, user `app`/`app`, root via socket |
| local DB | `school_app_prod` (33 tables) |
| Laravel connection | `prod_copy` in `config/database.php`, env keys `PRODCOPY_*` (see `.env`) |

**The instance lives in `/tmp` because AppArmor only lets mysqld write
`/var/lib/mysql`, `/var/log/mysql` and user tmp.** It does not survive a reboot.
Restart / reload:

```bash
mysqld --defaults-file=/tmp/prodtest/my.cnf &     # config already written
mysql --socket=/tmp/prodtest/prodtest.sock -uroot -e \
  "DROP DATABASE school_app_prod; CREATE DATABASE school_app_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql --socket=/tmp/prodtest/prodtest.sock -uroot school_app_prod \
  < ~/Downloads/u241077453_school_app_10_2026.sql            # ~4 min here
php artisan migrate --force --database=prod_copy             # ~19 s, see below
```

Reverting a rehearsal = `migrate:rollback --database=prod_copy` (keeps the archive)
or drop + reload the dump.

Current state of the copy: migrated (15,444 rows) **plus** 10 rows of manual
archive/restore tests by me — reload the dump for a pristine run.

## Pending migrations (exactly two)

- `2026_09_10_000000_dedupe_and_unique_exam_results` — rewritten, see below
- `2026_09_12_000000_create_fee_structures_table` — safe, creates an empty table

Orphans recorded in prod but absent locally (harmless no-ops):
`create_classes_table`, `create_exam_results_table`, `add_class_id_to_pupils_table`.

## The dedupe migration, as rewritten

Old shape (row-by-row PHP loop, `DELETE`s the losers):
1,998 groups × (1 select + 1 update + 1 delete) ≈ **6,000 statements, 288.6 s**
on this machine, and anything that disagreed between two rows was silently lost.

New shape (three statements, losers archived and never destroyed):

1. `CREATE TABLE exam_results_archive LIKE exam_results` (+ `kept_id`, `reason`,
   `archived_at`, `archived_by`; `LIKE` copies no foreign keys, and the unique
   index is dropped from the archive if the source ever carried it).
2. One `INSERT IGNORE ... SELECT` archives every row that is not the keeper,
   tagging it `duplicate` (all seven mark/comment fields agree) or `conflict`
   (any of them disagree), with `kept_id` pointing at the row that stayed.
   Keeper = **highest total marks** (`mid_term_mark + end_of_term_mark`), ties to
   the newest `updated_at`, then highest `id` — a merge can never lower a result.
3. `DELETE ... WHERE id IN (SELECT id FROM exam_results_archive WHERE reason IN
   ('duplicate','conflict'))` — deletes exactly what was archived, nothing else.
4. `ADD UNIQUE exam_results_pupil_subject_term_unique (pupil_id, subject_id, term)`.

### Measured on the real dump

| | old migration | new migration |
|---|---|---|
| wall time | 288,598 ms | **12,199 ms** (24× faster) |
| statements | ~6,000 | 4 |
| rows removed from `exam_results` | 3,447 (deleted) | 3,447 (**archived**) |
| recoverable | no | yes, `exam_results_archive` |
| conflicts identified | no | yes, 307 rows |

Verified after the run: live 15,453 + archived 3,447 = 18,900 (original row count,
nothing lost); 0 duplicate groups left; every `kept_id` resolves to a live row;
archive has no unique constraint; duplicate insert now fails with `23000 / 1062`.

Highest-mark keeper, measured against the dump: the rule changes *which* row
survives in only 90 of the 15,453 groups, and across the table it keeps 2,443 more
mark points (mean per pupil-subject-term 74.41 → 74.56). After the run,
**0 groups keep a row whose total is worse than a discarded row**; 34 groups still
have a higher mark in one column on the losing row (e.g. higher mid-term, lower
end-of-term), which is inherent to holding one row per key and is recoverable from
the archive.

The 307 conflict rows are genuinely different results, not near-identical ones:
the total absolute mark difference across them is 11,397 points.

## App-side change: results are never destroyed

New `app/Services/ExamResultArchive.php` — `move()` / `moveQuery()` copy rows into
the archive and delete exactly those rows, in one transaction.

Call sites converted (all previously hard-deleted):
- `ExamController::destroyTerm()` — 0 rows gone, archived as `manual`
- `ExamController::destroy()` — archived as `manual`
- `ExamController::updateTerm()` — clearing both halves of a mark archives instead
- `Pupil::deleting` — a deleted pupil's results are archived (`pupil_deleted`),
  they no longer vanish with the pupil

`ExamController` bulk save now uses `updateOrCreate()` instead of
`where($key)->first()` + `create()`, and a lost race retries as an update
(`QueryException` 23000 / 1062). Without that, two teachers saving the same pupil
at the same moment would now get a 500 instead of the old silent duplicate.

Restore one result:
```sql
INSERT INTO exam_results (id, term, mid_term_mark, mid_term_raw, mid_term_max,
  end_of_term_mark, end_term_raw, end_term_max, comments, subject_id, pupil_id,
  created_at, updated_at)
SELECT id, term, mid_term_mark, mid_term_raw, mid_term_max, end_of_term_mark,
  end_term_raw, end_term_max, comments, subject_id, pupil_id, created_at, updated_at
FROM exam_results_archive WHERE id = ?;
```
Reviewed-tested on the copy: archive → delete → restore round-trips cleanly.

Review the conflicts:
```sql
SELECT a.pupil_id, a.subject_id, a.term, a.kept_id,
       a.mid_term_mark AS lost_mid, e.mid_term_mark AS kept_mid,
       a.end_of_term_mark AS lost_end, e.end_of_term_mark AS kept_end
FROM exam_results_archive a JOIN exam_results e ON e.id = a.kept_id
WHERE a.reason = 'conflict' ORDER BY a.pupil_id, a.subject_id;
```
Archive can be dropped at any time (`DROP TABLE exam_results_archive`) — it is
never on a read path. Nothing prunes it automatically.

## Behaviour changes worth knowing

- **Read path fixed.** Before: each `ExamResult::where(pupil,subject,term)->first()`
  was `type: ALL, rows: 15,236` (no composite index existed). After: `type: const,
  rows: 1, key: exam_results_pupil_subject_term_unique`. A 40×8 subject save went
  from ~4.8 M row reads to 320 index lookups. Index is wide (key_len 1038) because
  `term` is `varchar(255)`; narrow it if the table ever gets big.
- **Term casing.** `exam_results.term` is lower-case (`term 1/2/3`), `payments.term`
  is title-case (`Term 1/2/3`). `utf8mb4_unicode_ci` makes them equal, which is why
  the `GROUP BY` and the unique index agree — no post-dedupe failures.
- `PupilController::store()` auto-creates pending `Payment` rows from the matching
  `fee_structures`; the table ships empty, so nothing is charged until an admin
  creates structures.
- `SmsWorker`/queue: `SendResultsSmsJob` + `ResultsFormatter` changed, but
  `deploy.yml` never runs `queue:restart` — workers keep the old code.
- `Pupil::deleting` still hard-deletes `payments` (money records). Same
  archive-before-delete treatment not applied there yet.

## Deploy risks

1. Backup first, one statement, no excuse not to:
   `CREATE TABLE exam_results_backup_YYYYMMDD AS SELECT * FROM exam_results;`
2. The migration is now ~11 s of work, but `deploy.yml` wraps it in
   `artisan down` → `up` with no rollback path. Keep `php artisan up` last.
3. Add `php artisan queue:restart` to the production deploy step.
4. `down()` drops only the unique index and deliberately keeps the archive.

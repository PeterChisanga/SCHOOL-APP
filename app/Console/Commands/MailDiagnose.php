<?php

namespace App\Console\Commands;

use App\Models\School;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * One command that answers "why do no emails arrive?" on a given host.
 *
 * Nothing is guessed: it prints the mail config the application is actually
 * using (not what .env says), whether config:cache is shadowing .env, whether
 * the SMTP host/port is reachable, whether a real send is accepted, and the
 * mail-related lines from the log. Every step prints PASS / WARN / FAIL so the
 * output can be grepped.
 *
 *   php artisan mail:diagnose                 # uses config('mail.notify_address')
 *   php artisan mail:diagnose me@example.com  # send to someone specific
 *   php artisan mail:diagnose --no-send       # everything except the send
 */
class MailDiagnose extends Command
{
    protected $signature = 'mail:diagnose
                            {recipient? : address to send the test email to}
                            {--no-send : skip the real send (config + connectivity only)}
                            {--log-lines=40 : how many recent log lines to scan}';

    protected $description = 'Diagnose outgoing email configuration and delivery';

    private int $failures = 0;

    private int $warnings = 0;

    public function handle(): int
    {
        $this->line('Application');
        $this->check(app()->environment(), config('app.url'), 'environment / url');
        $this->check(
            app()->configurationIsCached() ? 'CACHED' : 'not cached',
            'bootstrap/cache/config.php',
            app()->configurationIsCached() ? 'warn' : 'pass',
            app()->configurationIsCached()
                ? 'env() is NOT being read — edits to .env are ignored. Run: php artisan config:clear'
                : null
        );

        $mailer = config('mail.default');
        $host   = config("mail.mailers.{$mailer}.host");
        $port   = config("mail.mailers.{$mailer}.port");
        $crypto = config("mail.mailers.{$mailer}.encryption");
        $user   = config("mail.mailers.{$mailer}.username");
        $pass   = config("mail.mailers.{$mailer}.password");

        $this->newLine();
        $this->line('Mail configuration (what the app is actually using)');
        $this->check($mailer, 'mail.default');
        $this->check($host, 'mailer.host');
        $this->check($port, 'mailer.port');
        $this->check($crypto ?: '(none)', 'mailer.encryption');
        $this->check($user ?: '(none)', 'mailer.username');
        $this->check(
            empty($pass) ? 'EMPTY' : 'set ('.strlen((string) $pass).' chars)',
            'mailer.password',
            empty($pass) ? 'warn' : 'pass',
            empty($pass) ? 'SMTP auth will fail on 530/535 if the server requires it' : null
        );
        $this->check(config('mail.from.address'), 'mail.from.address');

        if (! in_array($mailer, ['smtp', 'sendmail', 'log', 'array'], true)) {
            $this->warn('  driver is a third-party transport; host/port checks below do not apply');
        }

        if (in_array($mailer, ['log', 'array'], true)) {
            $this->check($mailer, 'mail.default', 'fail', 'messages are written to the log, never sent');
        }

        $this->newLine();
        $this->line('Reachability');
        if ($host && ! filter_var($host, FILTER_VALIDATE_IP)) {
            $ip = gethostbyname($host);
            $ip === $host
                ? $this->check('unresolved', "DNS {$host}", 'fail', 'the SMTP hostname does not resolve from this server')
                : $this->check($ip, "DNS {$host}");
        }

        if ($host && $port && in_array($mailer, ['smtp'], true)) {
            $this->checkTcp($host, (int) $port, $crypto);
        }

        $recipient = $this->argument('recipient') ?: config('mail.notify_address');
        $this->newLine();
        $this->line('Recipients');
        $this->check($recipient, 'default notification address');

        try {
            $total   = School::count();
            $with    = School::whereNotNull('email')->where('email', '<>', '')->count();
            $this->check("{$with} of {$total}", 'schools with an email on file',
                $with === 0 ? 'fail' : ($with < $total / 2 ? 'warn' : 'pass'),
                $with < $total ? 'payment alerts to schools are skipped when schools.email is empty' : null);
        } catch (\Throwable $e) {
            $this->check('unavailable', 'schools.email coverage', 'warn', $e->getMessage());
        }

        if (! $this->option('no-send')) {
            $this->newLine();
            $this->line('Real send');
            $this->sendTest((string) $recipient);
        }

        $this->newLine();
        $this->logTail((int) $this->option('log-lines'));

        $this->newLine();
        if ($this->failures > 0) {
            $this->error("RESULT: {$this->failures} FAIL, {$this->warnings} WARN");

            return self::FAILURE;
        }

        $this->info("RESULT: PASS".($this->warnings ? ", {$this->warnings} WARN" : ''));

        return self::SUCCESS;
    }

    private function checkTcp(string $host, int $port, ?string $crypto): void
    {
        $target = ($crypto === 'ssl' ? 'ssl://' : '')."{$host}:{$port}";
        $errno  = 0;
        $errstr = '';
        $socket = @stream_socket_client($target, $errno, $errstr, 8);

        if ($socket === false) {
            $this->check('unreachable', "TCP {$target}", 'fail', trim($errstr) ?: "errno {$errno}");
            $this->line('         common causes: 587 blocked on the plan, wrong port/encryption pair (465+ssl, 587+tls), or the host blocks outbound SMTP entirely');

            return;
        }

        $greeting = fgets($socket, 512);
        fclose($socket);

        $this->check(
            'open'.($greeting ? ' — '.trim(substr($greeting, 0, 60)) : ''),
            "TCP {$target}",
            'pass'
        );
    }

    private function sendTest(string $recipient): void
    {
        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $this->check('invalid', "recipient {$recipient}", 'fail', 'not a valid address — Mail::to() would throw');

            return;
        }

        try {
            Mail::raw(
                "e-school mail diagnostic\n\nSent from ".config('app.url').' at '.now()->toDateTimeString().".",
                fn ($message) => $message->to($recipient)->subject('e-school mail diagnostic')
            );
        } catch (\Throwable $e) {
            $this->check('FAILED', 'send to '.$recipient, 'fail', get_class($e).': '.$e->getMessage());

            return;
        }

        $this->check('accepted by the mailer', 'send to '.$recipient);
        $this->line('         if it never arrives: check the mailbox Sent folder (was it handed to SMTP?),');
        $this->line('         the recipient spam folder, and SPF/DKIM for the from-domain.');
    }

    private function logTail(int $lines): void
    {
        $path = storage_path('logs/laravel.log');

        $this->line("Log scan ({$path})");

        if (! is_file($path)) {
            $this->check('missing', 'log file', 'warn', 'no log yet — nothing has failed here, or logging goes elsewhere');

            return;
        }

        $this->check(gmdate('Y-m-d H:i:s', filemtime($path)).' UTC', 'last log write');

        $tail = $this->tail($path, 4000);
        $hits = [];

        foreach (explode("\n", $tail) as $line) {
            if (preg_match('/Failed to send|email|smtp|530|535|550|554|EHLO|stream_socket|Connection (could not|refused|timed out)/iu', $line)) {
                $hits[] = $line;
            }
        }

        $hits = array_slice($hits, -$lines);

        if (empty($hits)) {
            $this->check('no mail-related lines', 'recent log entries');
            $this->checkFailedJobs();

            return;
        }

        $this->checkFailedJobs();

        $this->check(count($hits).' line(s)', 'recent mail-related log entries', 'warn');
        foreach ($hits as $hit) {
            $this->line('         '.mb_substr(trim($hit), 0, 300));
        }
    }

    private function checkFailedJobs(): void
    {
        try {
            $failed = DB::table('failed_jobs')->count();
        } catch (\Throwable $e) {
            return;
        }

        if ($failed > 0) {
            $this->check($failed, 'failed_jobs rows', 'warn', 'a queued job failed — php artisan queue:failed');
        }
    }

    private function tail(string $path, int $bytes): string
    {
        $size = filesize($path);
        $fh   = fopen($path, 'r');

        if ($size > $bytes) {
            fseek($fh, -$bytes, SEEK_END);
        }

        $data = stream_get_contents($fh);
        fclose($fh);

        return (string) $data;
    }

    private function check($value, string $label, string $state = 'pass', ?string $hint = null): void
    {
        $tag = match ($state) {
            'fail' => '<fg=red>FAIL</>',
            'warn' => '<fg=yellow>WARN</>',
            default => '<fg=green>PASS</>',
        };

        if ($state === 'fail') {
            $this->failures++;
        } elseif ($state === 'warn') {
            $this->warnings++;
        }

        $value = is_null($value) || $value === '' ? '(empty)' : (string) $value;

        $this->line("  {$tag}  {$label}: {$value}");

        if ($hint) {
            $this->line("         {$hint}");
        }

        if ($value === '(empty)' && $state === 'pass') {
            $this->warnings++;
        }
    }
}

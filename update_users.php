<?php
/**
 * update_users.php
 *
 * Idempotent script to update or insert two users so they can log in.
 * - Hashes passwords with password_hash()
 * - Updates updated_at = NOW()
 * - Safe to run multiple times without errors
 *
 * Configure DB connection below or via environment variables:
 *   DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_CHARSET
 *
 * Run: php update_users.php
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// --- Configuration (edit or set environment variables) ---
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbName = getenv('DB_NAME') ?: 'your_database';
$dbUser = getenv('DB_USER') ?: 'your_db_user';
$dbPass = getenv('DB_PASS') ?: 'your_db_password';
$dbCharset = getenv('DB_CHARSET') ?: 'utf8mb4';
$dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";

// Users to update
$targets = [
    [
        'first_name' => 'COMFORT',
        'last_name'  => 'LIMATA',
        'email'      => 'teacher2@gmail.com',
        'password'   => 'comfort.lee',
    ],
    [
        'first_name' => 'KAKUMBI',
        'last_name'  => 'LIMATA',
        'email'      => 'kapini@gmail.com',
        'password'   => 'comfort.lee',
    ],
];

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    fwrite(STDERR, "Database connection failed: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

// Prepared statements
// Find by first_name + last_name
$findByName = $pdo->prepare('SELECT id, email FROM users WHERE first_name = ? AND last_name = ?');
// Find by email
$findByEmail = $pdo->prepare('SELECT id, first_name, last_name FROM users WHERE email = ?');
// Update email + password
$updateEmailAndPassword = $pdo->prepare('UPDATE users SET email = ?, password = ?, updated_at = NOW() WHERE id = ?');
// Update password only
$updatePasswordOnly = $pdo->prepare('UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?');
// Insert new user (assumes table has only the columns first_name, last_name, email, password, created_at, updated_at)
$insertUser = $pdo->prepare('INSERT INTO users (first_name, last_name, email, password, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())');

$summary = [
    'updated' => [],
    'password_only' => [],
    'inserted' => [],
    'errors' => [],
];

foreach ($targets as $t) {
    $fname = $t['first_name'];
    $lname = $t['last_name'];
    $targetEmail = $t['email'];
    $plainPassword = $t['password'];

    // Hash the password
    $hashed = password_hash($plainPassword, PASSWORD_DEFAULT);

    try {
        // Look up users with the same name
        $findByName->execute([$fname, $lname]);
        $rows = $findByName->fetchAll();

        if (count($rows) > 0) {
            // Update all matching users (idempotent)
            foreach ($rows as $row) {
                $userId = $row['id'];
                $currentEmail = $row['email'];

                // Check whether desired email is owned by another user
                $findByEmail->execute([$targetEmail]);
                $emailOwner = $findByEmail->fetch();

                $canUpdateEmail = true;
                if ($emailOwner && (int)$emailOwner['id'] !== (int)$userId) {
                    // Email taken by another user, avoid changing email to prevent unique constraint violation
                    $canUpdateEmail = false;
                }

                if ($canUpdateEmail) {
                    $updateEmailAndPassword->execute([$targetEmail, $hashed, $userId]);
                    $summary['updated'][] = ['id' => $userId, 'first_name' => $fname, 'last_name' => $lname, 'email' => $targetEmail];
                    echo "Updated user id={$userId} ({$fname} {$lname}): set email={$targetEmail} and updated password." . PHP_EOL;
                } else {
                    // Only update password
                    $updatePasswordOnly->execute([$hashed, $userId]);
                    $summary['password_only'][] = ['id' => $userId, 'first_name' => $fname, 'last_name' => $lname, 'email' => $currentEmail];
                    echo "Updated password for user id={$userId} ({$fname} {$lname}) but skipped email change because {$targetEmail} is owned by another account." . PHP_EOL;
                }
            }
        } else {
            // No user found by name — insert new user if target email not taken
            $findByEmail->execute([$targetEmail]);
            $emailOwner = $findByEmail->fetch();
            if ($emailOwner) {
                // Email is taken. Modify email for insertion to avoid collision
                $modifiedEmail = modifyEmailForInsert($targetEmail);
                $insertUser->execute([$fname, $lname, $modifiedEmail, $hashed]);
                $newId = (int)$pdo->lastInsertId();
                $summary['inserted'][] = ['id' => $newId, 'first_name' => $fname, 'last_name' => $lname, 'email' => $modifiedEmail];
                echo "Inserted new user id={$newId} ({$fname} {$lname}) with email={$modifiedEmail} because {$targetEmail} was already taken." . PHP_EOL;
            } else {
                $insertUser->execute([$fname, $lname, $targetEmail, $hashed]);
                $newId = (int)$pdo->lastInsertId();
                $summary['inserted'][] = ['id' => $newId, 'first_name' => $fname, 'last_name' => $lname, 'email' => $targetEmail];
                echo "Inserted new user id={$newId} ({$fname} {$lname}) with email={$targetEmail}." . PHP_EOL;
            }
        }
    } catch (PDOException $e) {
        $summary['errors'][] = ['first_name' => $fname, 'last_name' => $lname, 'error' => $e->getMessage()];
        fwrite(STDERR, "Error processing {$fname} {$lname}: " . $e->getMessage() . PHP_EOL);
    }
}

// Print summary
echo PHP_EOL . "Summary:" . PHP_EOL;
echo "  Updated (email+password): " . count($summary['updated']) . PHP_EOL;
foreach ($summary['updated'] as $u) {
    echo "    - id={$u['id']} {$u['first_name']} {$u['last_name']} ({$u['email']})" . PHP_EOL;
}

echo "  Password-only updates (email owned by another): " . count($summary['password_only']) . PHP_EOL;
foreach ($summary['password_only'] as $u) {
    echo "    - id={$u['id']} {$u['first_name']} {$u['last_name']} (current email={$u['email']})" . PHP_EOL;
}

echo "  Inserted: " . count($summary['inserted']) . PHP_EOL;
foreach ($summary['inserted'] as $i) {
    echo "    - id={$i['id']} {$i['first_name']} {$i['last_name']} ({$i['email']})" . PHP_EOL;
}

if (!empty($summary['errors'])) {
    echo "  Errors: " . count($summary['errors']) . PHP_EOL;
    foreach ($summary['errors'] as $err) {
        echo "    - {$err['first_name']} {$err['last_name']}: {$err['error']}" . PHP_EOL;
    }
}

// Helper: modify email to avoid collisions by adding +import_TIMESTAMP before the @
function modifyEmailForInsert(string $email): string
{
    $parts = explode('@', $email);
    if (count($parts) !== 2) {
        return $email . '+import_' . time();
    }
    $local = preg_replace('/[^A-Za-z0-9._+-]/', '', $parts[0]);
    $domain = $parts[1];
    return $local . '+import_' . time() . '@' . $domain;
}

exit(0);


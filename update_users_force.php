<?php
/**
 * update_users_force.php
 *
 * Force-update passwords for two existing users by email so they can log in.
 * - Overwrites password using password_hash(..., PASSWORD_BCRYPT)
 * - Sets updated_at = NOW()
 * - If a user with the email doesn't exist, the script will insert it (using provided first/last names)
 *
 * Configure DB connection via environment variables or edit the defaults below.
 * Run: php update_users_force.php
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

// Target updates by email (force update)
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

$selectByEmail = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$updatePassword = $pdo->prepare('UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?');
$insertUser = $pdo->prepare('INSERT INTO users (first_name, last_name, email, password, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())');

foreach ($targets as $t) {
    $email = $t['email'];
    $fname = $t['first_name'];
    $lname = $t['last_name'];
    $plain = $t['password'];

    // Use bcrypt explicitly for compatibility
    $hash = password_hash($plain, PASSWORD_BCRYPT);

    try {
        $selectByEmail->execute([$email]);
        $row = $selectByEmail->fetch();
        if ($row) {
            $id = (int)$row['id'];
            $updatePassword->execute([$hash, $id]);
            echo "Updated password for existing user id={$id} ({$email})." . PHP_EOL;
        } else {
            $insertUser->execute([$fname, $lname, $email, $hash]);
            $newId = (int)$pdo->lastInsertId();
            echo "Inserted new user id={$newId} with email={$email}." . PHP_EOL;
        }
    } catch (PDOException $e) {
        fwrite(STDERR, "Error processing {$email}: " . $e->getMessage() . PHP_EOL);
    }
}

echo PHP_EOL . "Done. Try logging in with the provided emails and password 'comfort.lee'." . PHP_EOL;
exit(0);


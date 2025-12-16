<?php
/**
 * check_password.php
 *
 * Usage (cmd.exe):
 *   php check_password.php DB_HOST DB_NAME DB_USER DB_PASS EMAIL PASSWORD
 * Example:
 *   php check_password.php 127.0.0.1 mydb root secret teacher2@gmail.com comfort.lee
 *
 * Alternatively, set environment variables DB_HOST, DB_NAME, DB_USER, DB_PASS and call:
 *   php check_password.php EMAIL PASSWORD
 */

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    echo "This script must be run from the CLI.\n";
    exit(1);
}

$args = $argv;
array_shift($args); // remove script name

if (count($args) === 2) {
    // Use env vars for DB
    $dbHost = getenv('DB_HOST') ?: null;
    $dbName = getenv('DB_NAME') ?: null;
    $dbUser = getenv('DB_USER') ?: null;
    $dbPass = getenv('DB_PASS') ?: null;
    $email = $args[0];
    $plain = $args[1];
} elseif (count($args) === 6) {
    [$dbHost, $dbName, $dbUser, $dbPass, $email, $plain] = $args;
} else {
    echo "Usage:\n";
    echo "  php check_password.php DB_HOST DB_NAME DB_USER DB_PASS EMAIL PASSWORD\n";
    echo "or set DB_HOST/DB_NAME/DB_USER/DB_PASS and run:\n";
    echo "  php check_password.php EMAIL PASSWORD\n";
    exit(1);
}

if (!$dbHost || !$dbName || !$dbUser) {
    fwrite(STDERR, "Database credentials are missing. Set env vars or pass as args.\n");
    exit(1);
}

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    fwrite(STDERR, "DB connect error: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

$stmt = $pdo->prepare('SELECT id, email, password FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    echo "No user found for email: {$email}\n";
    exit(0);
}

$hash = $row['password'] ?? '';
if (!$hash) {
    echo "User has no password hash stored.\n";
    exit(1);
}

echo "Found user id={$row['id']} email={$row['email']}\n";
echo "Password hash: {$hash}\n";
$ok = password_verify($plain, $hash);
echo $ok ? "password_verify: MATCH" . PHP_EOL : "password_verify: MISMATCH" . PHP_EOL;
exit(0);


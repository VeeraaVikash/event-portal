<?php
// Database connection.
//
// Credentials are never stored in this file. They are read from the
// environment first (DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT,
// DB_SOCKET), then from includes/config.local.php - an untracked file holding
// the values for this machine. See includes/config.local.example.php.

$configFile = __DIR__ . '/config.local.php';
$local = is_file($configFile) ? require $configFile : [];

$envPassword = getenv('DB_PASSWORD');

$host     = getenv('DB_HOST')   ?: ($local['host']   ?? 'localhost');
$user     = getenv('DB_USER')   ?: ($local['user']   ?? '');
// An empty password is legitimate, so only fall back when the variable is unset.
$password = $envPassword !== false ? $envPassword : ($local['password'] ?? '');
$dbname   = getenv('DB_NAME')   ?: ($local['dbname'] ?? '');
$port     = (int) (getenv('DB_PORT') ?: ($local['port'] ?? 3306));
$socket   = getenv('DB_SOCKET') ?: ($local['socket'] ?? null);

$conn = new mysqli($host, $user, $password, $dbname, $port, $socket);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

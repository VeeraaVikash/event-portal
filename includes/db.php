<?php
$host = 'localhost';
$user = 'eventadmin';
$password = 'eventpass';
$dbname = 'event';
$socket = '/Users/user49/homebrew/mysql-local/tmp/mysql.sock';

$conn = new mysqli($host, $user, $password, $dbname, 3306, $socket);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

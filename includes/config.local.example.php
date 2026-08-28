<?php
// Template for includes/config.local.php.
//
// Copy this file to includes/config.local.php and fill in the values for your
// machine. config.local.php is ignored by git so credentials stay off GitHub.
//
//     cp includes/config.local.example.php includes/config.local.php
//
// Every value can also be supplied through the environment instead, which
// takes precedence: DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT, DB_SOCKET.
return [
    'host'     => 'localhost',
    'user'     => 'your_db_user',
    'password' => 'your_db_password',
    'dbname'   => 'event',
    'port'     => 3306,
    // Path to the MySQL unix socket, or null to connect over TCP.
    'socket'   => null,
];

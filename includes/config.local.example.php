<?php
// Template for includes/config.local.php.
//
// Copy this file to includes/config.local.php and fill in the values for your
// machine. config.local.php is ignored by git so credentials stay off GitHub.
//
//     cp includes/config.local.example.php includes/config.local.php
//
// Every value can also be supplied through the environment instead, which
// takes precedence: DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT, DB_SOCKET,
// APP_URL, SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASSWORD, SMTP_SECURE,
// SMTP_FROM, SMTP_FROM_NAME.
return [
    'host'     => 'localhost',
    'user'     => 'your_db_user',
    'password' => 'your_db_password',
    'dbname'   => 'event',
    'port'     => 3306,
    // Path to the MySQL unix socket, or null to connect over TCP.
    'socket'   => null,

    // Absolute URL of this installation, with no trailing slash.
    //
    // Reminder e-mails and the QR codes printed in a report's annexure are read
    // away from the browser that produced them, so they cannot use a relative
    // path. Cron has no request to infer this from, so set it explicitly.
    'app_url'  => 'http://localhost/eventconnect',

    // Outgoing mail for the event reminders. Leave 'host' empty to disable
    // sending entirely: the reminder sweep then does nothing instead of failing.
    //
    // Gmail needs an App Password (Google Account > Security > 2-Step
    // Verification > App passwords), not the account password.
    'smtp' => [
        'host'      => '',              // e.g. smtp.gmail.com
        'port'      => 587,             // 587 with 'tls', 465 with 'ssl'
        'user'      => '',              // full address, e.g. events@srmist.edu.in
        'password'  => '',              // app password
        'secure'    => 'tls',           // 'tls' (STARTTLS), 'ssl', or 'none'
        'from'      => '',              // defaults to 'user' when empty
        'from_name' => 'SRM Event Connect',
        'timeout'   => 15,
    ],
];

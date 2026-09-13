<?php
/**
 * Minimal SMTP client.
 *
 * The project has no Composer and no local MTA - PHP's mail() silently
 * discards everything on a default XAMPP install - so reminders speak SMTP
 * directly to whatever server the deployment configures. That is a few hundred
 * lines less than vendoring PHPMailer and covers what this application sends:
 * one short multipart message at a time.
 *
 * Credentials never live in the repository. They come from the environment
 * (SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASSWORD, SMTP_SECURE, SMTP_FROM,
 * SMTP_FROM_NAME) or from includes/config.local.php under an 'smtp' key.
 * See includes/config.local.example.php.
 */

/** Resolved SMTP settings, environment first, then config.local.php. */
function ec_mail_config(): array
{
    static $config = null;
    if ($config !== null) {
        return $config;
    }

    $configFile = __DIR__ . '/config.local.php';
    $local = is_file($configFile) ? require $configFile : [];
    $smtp  = $local['smtp'] ?? [];

    $env = function (string $name, $fallback) {
        $value = getenv($name);
        return ($value === false || $value === '') ? $fallback : $value;
    };

    $config = [
        'host'      => $env('SMTP_HOST', $smtp['host'] ?? ''),
        'port'      => (int) $env('SMTP_PORT', $smtp['port'] ?? 587),
        'user'      => $env('SMTP_USER', $smtp['user'] ?? ''),
        'password'  => $env('SMTP_PASSWORD', $smtp['password'] ?? ''),
        // 'tls' = STARTTLS on 587, 'ssl' = implicit TLS on 465, 'none' = plain.
        'secure'    => strtolower((string) $env('SMTP_SECURE', $smtp['secure'] ?? 'tls')),
        'from'      => $env('SMTP_FROM', $smtp['from'] ?? ($smtp['user'] ?? '')),
        'from_name' => $env('SMTP_FROM_NAME', $smtp['from_name'] ?? 'SRM Event Connect'),
        'timeout'   => (int) $env('SMTP_TIMEOUT', $smtp['timeout'] ?? 15),
    ];
    return $config;
}

/** True when enough is configured to attempt a send. */
function ec_mail_configured(): bool
{
    $c = ec_mail_config();
    return $c['host'] !== '' && $c['from'] !== '';
}

/** Reads one SMTP reply, following continuation lines ("250-" ... "250 "). */
function ec_smtp_read($socket): string
{
    $reply = '';
    while (($line = fgets($socket, 1024)) !== false) {
        $reply .= $line;
        // A space in the fourth column marks the final line of the reply.
        if (strlen($line) < 4 || $line[3] === ' ') {
            break;
        }
    }
    return $reply;
}

/**
 * Sends one command and checks its reply code.
 *
 * @throws RuntimeException when the server answers with anything else.
 */
function ec_smtp_command($socket, string $command, array $expected, string $label): string
{
    if ($command !== '') {
        fwrite($socket, $command . "\r\n");
    }
    $reply = ec_smtp_read($socket);
    $code  = (int) substr($reply, 0, 3);
    if (!in_array($code, $expected, true)) {
        throw new RuntimeException($label . ' failed: ' . trim($reply));
    }
    return $reply;
}

/** RFC 2047 encoding, so accented names and subjects survive the transport. */
function ec_mail_encode_header(string $text): string
{
    if (preg_match('/^[\x20-\x7E]*$/', $text)) {
        return $text;
    }
    return '=?UTF-8?B?' . base64_encode($text) . '?=';
}

/** "Name <address>", with the name encoded and quoted only when needed. */
function ec_mail_address(string $email, string $name = ''): string
{
    if ($name === '') {
        return $email;
    }
    return '"' . str_replace('"', '', ec_mail_encode_header($name)) . '" <' . $email . '>';
}

/**
 * Sends a single multipart (plain + HTML) message over SMTP.
 *
 * Never throws: reminders run unattended, and a mail server that is down must
 * not take a dashboard page or a cron run with it.
 *
 * @return array{0:bool,1:string} [sent, error message when not sent]
 */
function ec_mail_send(string $toEmail, string $toName, string $subject, string $html, string $text): array
{
    $c = ec_mail_config();

    if (!ec_mail_configured()) {
        return [false, 'SMTP is not configured (see includes/config.local.example.php)'];
    }
    if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        return [false, 'Invalid recipient address'];
    }

    $transport = ($c['secure'] === 'ssl') ? 'ssl://' : '';
    $socket = @stream_socket_client(
        $transport . $c['host'] . ':' . $c['port'],
        $errNo,
        $errStr,
        $c['timeout'],
        STREAM_CLIENT_CONNECT
    );
    if (!$socket) {
        return [false, sprintf('Cannot reach %s:%d (%s)', $c['host'], $c['port'], $errStr ?: $errNo)];
    }
    stream_set_timeout($socket, $c['timeout']);

    // The local host name the server sees. Anything resolvable is fine.
    $helo = $_SERVER['SERVER_NAME'] ?? (gethostname() ?: 'localhost');

    try {
        ec_smtp_command($socket, '', [220], 'Greeting');
        ec_smtp_command($socket, 'EHLO ' . $helo, [250], 'EHLO');

        if ($c['secure'] === 'tls') {
            ec_smtp_command($socket, 'STARTTLS', [220], 'STARTTLS');
            $crypto = STREAM_CRYPTO_METHOD_TLS_CLIENT;
            if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
                $crypto |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            }
            if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
                $crypto |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
            }
            if (!stream_socket_enable_crypto($socket, true, $crypto)) {
                throw new RuntimeException('TLS negotiation failed');
            }
            // The server's capability list is only trustworthy after the
            // channel is encrypted, so greet it again.
            ec_smtp_command($socket, 'EHLO ' . $helo, [250], 'EHLO (post-TLS)');
        }

        if ($c['user'] !== '') {
            ec_smtp_command($socket, 'AUTH LOGIN', [334], 'AUTH LOGIN');
            ec_smtp_command($socket, base64_encode($c['user']), [334], 'SMTP username');
            ec_smtp_command($socket, base64_encode($c['password']), [235], 'SMTP password');
        }

        ec_smtp_command($socket, 'MAIL FROM:<' . $c['from'] . '>', [250], 'MAIL FROM');
        ec_smtp_command($socket, 'RCPT TO:<' . $toEmail . '>', [250, 251], 'RCPT TO');
        ec_smtp_command($socket, 'DATA', [354], 'DATA');

        $boundary = 'ec-' . bin2hex(random_bytes(12));
        $headers = [
            'Date: ' . date('r'),
            'From: ' . ec_mail_address($c['from'], $c['from_name']),
            'To: ' . ec_mail_address($toEmail, $toName),
            'Subject: ' . ec_mail_encode_header($subject),
            'Message-ID: <' . bin2hex(random_bytes(16)) . '@' . $helo . '>',
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
            'X-Mailer: SRM Event Connect',
            'Auto-Submitted: auto-generated',
        ];

        $body = implode("\r\n", $headers) . "\r\n\r\n"
            . '--' . $boundary . "\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($text), 76, "\r\n")
            . '--' . $boundary . "\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($html), 76, "\r\n")
            . '--' . $boundary . "--\r\n";

        // A line consisting of a single dot would end the message early.
        $body = preg_replace('/^\./m', '..', $body);

        fwrite($socket, $body . "\r\n.\r\n");
        ec_smtp_command($socket, '', [250], 'Message body');

        @fwrite($socket, "QUIT\r\n");
        fclose($socket);
        return [true, ''];
    } catch (Throwable $e) {
        @fclose($socket);
        return [false, $e->getMessage()];
    }
}

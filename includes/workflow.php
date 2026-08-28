<?php
/**
 * Shared workflow helpers: CSRF tokens, safe error handling and the
 * server-side proposal state-transition rules.
 *
 * Deliberately small. It centralises rules that were previously duplicated or
 * missing across the api_*.php endpoints without introducing a framework.
 *
 * Response shapes are NOT changed here - each endpoint keeps emitting the exact
 * JSON structure its frontend caller already expects.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ------------------------------------------------------------------ CSRF */

/** Returns the per-session CSRF token, creating it on first use. */
function ec_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Reads the token supplied by the caller. Supports the header used by the
 * fetch() callers and the hidden field used by the two classic form POSTs.
 */
function ec_supplied_csrf_token(?array $jsonBody = null): string
{
    if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        return (string) $_SERVER['HTTP_X_CSRF_TOKEN'];
    }
    if (!empty($_POST['csrf_token'])) {
        return (string) $_POST['csrf_token'];
    }
    if ($jsonBody !== null && !empty($jsonBody['csrf_token'])) {
        return (string) $jsonBody['csrf_token'];
    }
    return '';
}

/** True when the request carries a valid CSRF token. */
function ec_csrf_valid(?array $jsonBody = null): bool
{
    $expected = $_SESSION['csrf_token'] ?? '';
    $supplied = ec_supplied_csrf_token($jsonBody);
    return $expected !== '' && $supplied !== '' && hash_equals($expected, $supplied);
}

/* ------------------------------------------------- safe error reporting */

/**
 * Logs the real exception server-side and returns a short opaque reference so
 * support can correlate it. Never returns driver text to the client.
 */
function ec_log_exception(Throwable $e, string $context): string
{
    $ref = substr(bin2hex(random_bytes(4)), 0, 8);
    error_log(sprintf('[eventconnect][%s][ref:%s] %s in %s:%d',
        $context, $ref, $e->getMessage(), $e->getFile(), $e->getLine()));
    return $ref;
}

/** Emits JSON with a correct content type, preserving the caller's shape. */
function ec_json(array $payload, int $httpCode = 200): void
{
    if (!headers_sent()) {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($payload);
}

/* ------------------------------------------------- workflow state rules */

/** Every status the application uses. 'Revision' is legacy and unused by code. */
const EC_STATUSES = ['Pending', 'Approved', 'Rejected', 'Review', 'Cancelled', 'Rescheduled'];

/** Maps an HOD action to the status it produces. */
const EC_HOD_ACTION_STATUS = [
    'approve' => 'Approved',
    'reject'  => 'Rejected',
    'review'  => 'Review',
];

/**
 * Decides whether an HOD action may be applied to a proposal in $current.
 *
 * Preserves the pre-existing rules - Cancelled proposals are frozen, and an
 * HOD may still revisit an earlier decision - while rejecting the no-op repeat
 * that previously let the same decision be recorded twice.
 *
 * @return array{0:bool,1:string} [allowed, reason-if-not]
 */
function ec_hod_transition_allowed(string $current, string $action): array
{
    if (!isset(EC_HOD_ACTION_STATUS[$action])) {
        return [false, 'Invalid action'];
    }
    if (strcasecmp($current, 'Cancelled') === 0) {
        return [false, 'Proposal is cancelled and cannot be modified.'];
    }
    $target = EC_HOD_ACTION_STATUS[$action];
    if (strcasecmp($current, $target) === 0) {
        return [false, "Proposal is already marked {$target}."];
    }
    return [true, ''];
}

/**
 * Decides whether a convener action may be applied.
 *
 * Preserves the existing rule that only Approved events may be rescheduled,
 * and blocks cancelling something already cancelled.
 *
 * @return array{0:bool,1:string} [allowed, reason-if-not]
 */
function ec_convener_transition_allowed(string $current, string $action): array
{
    if ($action === 'cancel') {
        if (strcasecmp($current, 'Cancelled') === 0) {
            return [false, 'Proposal is already cancelled.'];
        }
        return [true, ''];
    }
    if ($action === 'reschedule') {
        if ($current !== 'Approved') {
            return [false, 'Only Approved events can be explicitly rescheduled'];
        }
        return [true, ''];
    }
    return [false, 'Invalid action'];
}

/** Validates a Y-m-d date string, rejecting malformed or impossible dates. */
function ec_valid_date(?string $value): bool
{
    if (!is_string($value) || $value === '') {
        return false;
    }
    $d = DateTime::createFromFormat('Y-m-d', $value);
    return $d !== false && $d->format('Y-m-d') === $value;
}

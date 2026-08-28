<?php
/**
 * SRM Event Connect - backend health check.
 *
 *   php scripts/check_backend.php
 *   php scripts/check_backend.php --json
 *
 * Read-only. It inspects the environment, the application's PHP syntax and the
 * database structure. It never inserts, updates, deletes, migrates, repairs,
 * changes permissions, sends mail or triggers any business action.
 *
 * Exit codes:
 *   0  all required checks passed (WARN is allowed)
 *   1  at least one confirmed FAIL
 *   2  one or more required checks could not be completed (SKIP), no FAILs
 *
 * Limitations: this checks syntax and environment health only. It does not
 * exercise the workflow, authorization, concurrency or integration behaviour,
 * and passing it does not mean the backend is error-free.
 */

declare(strict_types=1);

// --------------------------------------------------------- CLI-only guard
// Checked before any configuration is loaded.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    header('Content-Type: text/plain');
    exit("Forbidden: check_backend.php is a command-line diagnostic and must not be exposed over HTTP.\n");
}

$ROOT = dirname(__DIR__);
$jsonMode = in_array('--json', $argv, true);

const ST_PASS = 'PASS';
const ST_WARN = 'WARN';
const ST_FAIL = 'FAIL';
const ST_SKIP = 'SKIP';

$results = [];
function record(string $name, string $status, string $detail): void {
    global $results;
    $results[] = ['check' => $name, 'status' => $status, 'detail' => $detail];
}

/* ------------------------------------------------------ 1. PHP runtime */

// Minimum reflects syntax actually used in the codebase: enum-free but relies
// on ?? / ?-> era features, str_contains-free, and `never` return type in
// api_upload_preloaded.php (PHP 8.1+).
$minVersion = '8.1.0';
if (version_compare(PHP_VERSION, $minVersion, '>=')) {
    record('PHP version', ST_PASS, PHP_VERSION . ' (>= ' . $minVersion . ')');
} else {
    record('PHP version', ST_FAIL,
        PHP_VERSION . ' is below the required ' . $minVersion .
        '. api_upload_preloaded.php uses the `never` return type (8.1+). Upgrade PHP.');
}

// Extensions derived from actual usage in the code.
$required = [
    'mysqli'   => 'includes/db.php opens a mysqli connection',
    'json'     => 'every api_*.php endpoint encodes/decodes JSON',
    'session'  => 'authentication state lives in $_SESSION',
    'mbstring' => 'mb_strlen/mb_substr in api_proposal_message.php and api_upload_preloaded.php',
];
foreach ($required as $ext => $why) {
    if (extension_loaded($ext)) {
        record("Extension: {$ext}", ST_PASS, $why);
    } else {
        record("Extension: {$ext}", ST_FAIL, "Missing. Needed because {$why}. Install/enable php-{$ext}.");
    }
}

if (extension_loaded('fileinfo')) {
    record('Extension: fileinfo', ST_PASS, 'available for richer upload inspection');
} else {
    record('Extension: fileinfo', ST_WARN,
        'Not loaded. Report uploads still validate the %PDF- signature directly, so this is optional.');
}

/* ------------------------------------------ 2. PHP syntax across the app */

$phpFiles = [];
$skipDirs = ['.git', 'node_modules', 'vendor', 'reports', 'backups', 'images', 'uploads'];
$it = new RecursiveIteratorIterator(
    new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($ROOT, FilesystemIterator::SKIP_DOTS),
        function ($current) use ($skipDirs) {
            if ($current->isDir()) {
                return !in_array($current->getFilename(), $skipDirs, true);
            }
            return strtolower($current->getExtension()) === 'php';
        }
    )
);
foreach ($it as $f) {
    $phpFiles[] = $f->getPathname();
}
sort($phpFiles);

// Determine whether we may run a subprocess at all.
$canExec = function_exists('proc_open')
    && !in_array('proc_open', array_map('trim', explode(',', (string) ini_get('disable_functions'))), true);

if (!$canExec) {
    record('PHP syntax', ST_SKIP,
        'proc_open is unavailable or disabled, so php -l could not be run. '
        . count($phpFiles) . ' file(s) were NOT checked. Re-run where process execution is permitted.');
} elseif (!$phpFiles) {
    record('PHP syntax', ST_WARN, 'No PHP files found to lint.');
} else {
    $phpBin = PHP_BINARY ?: 'php';
    $bad = [];
    foreach ($phpFiles as $file) {
        // Arguments are passed as a list, so filenames are never shell-parsed.
        $proc = proc_open(
            [$phpBin, '-n', '-d', 'display_errors=0', '-l', $file],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );
        if (!is_resource($proc)) {
            $bad[] = basename($file) . ': could not start linter';
            continue;
        }
        $out = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        if (proc_close($proc) !== 0) {
            // Prefer the "Parse error: ..." line; fall back to the first line.
            $lines = array_values(array_filter(array_map('trim', explode("\n", trim($out)))));
            $line = '';
            foreach ($lines as $l) {
                if (stripos($l, 'error') !== false && stripos($l, 'Errors parsing') === false) {
                    $line = $l;
                    break;
                }
            }
            if ($line === '') {
                $line = $lines[0] ?? 'syntax error';
            }
            // Keep paths relative so output is portable and reveals no layout.
            $line = str_replace($ROOT . '/', '', $line);
            $bad[] = str_replace($ROOT . '/', '', $file) . ' -> ' . $line;
        }
    }
    if ($bad) {
        record('PHP syntax', ST_FAIL,
            count($bad) . ' of ' . count($phpFiles) . " file(s) failed to parse:\n    - " . implode("\n    - ", $bad));
    } else {
        record('PHP syntax', ST_PASS, count($phpFiles) . ' file(s) parsed cleanly');
    }
}

/* ------------------------------------------- 3. Required files & config */

$requiredFiles = [
    'includes/db.php'       => 'database connection settings',
    'includes/workflow.php' => 'CSRF and state-transition rules',
    'index.php'             => 'public landing page',
    'login.php'             => 'authentication entry point',
    'proposal.php'          => 'proposal create/edit controller',
];
$missingFiles = [];
foreach ($requiredFiles as $rel => $why) {
    if (!is_file($ROOT . '/' . $rel)) {
        $missingFiles[] = "{$rel} ({$why})";
    }
}
if ($missingFiles) {
    record('Required files', ST_FAIL, 'Missing: ' . implode('; ', $missingFiles));
} else {
    record('Required files', ST_PASS, count($requiredFiles) . ' core file(s) present');
}

// Load DB config in an isolated scope. includes/db.php only assigns variables
// and opens a connection - it emits no output and runs no application logic -
// so requiring it here has no side effects beyond the connection itself.
$dbConfigOk = false;
$connectionAttempted = false;
$conn = null;
$dbName = null;
if (is_file($ROOT . '/includes/db.php')) {
    $loader = static function (string $path): array {
        $host = $user = $password = $dbname = $socket = null;
        $conn = null;
        require $path;
        return ['conn' => $conn ?? null, 'dbname' => $dbname ?? null,
                'host' => $host ?? null, 'user' => $user ?? null,
                'password' => $password ?? null, 'socket' => $socket ?? null];
    };
    try {
        $cfg = $loader($ROOT . '/includes/db.php');
        // Report only presence, never the values themselves.
        $missingCfg = [];
        foreach (['host', 'user', 'dbname'] as $k) {
            if (empty($cfg[$k])) {
                $missingCfg[] = $k;
            }
        }
        if ($cfg['password'] === null) {
            $missingCfg[] = 'password';
        }
        if ($missingCfg) {
            record('Database configuration', ST_FAIL,
                'Not set in includes/db.php: ' . implode(', ', $missingCfg));
        } else {
            $pwNote = $cfg['password'] === '' ? ' (password is empty - acceptable only for local dev)' : '';
            record('Database configuration', ST_PASS, 'host, user, password and dbname are all set' . $pwNote);
            $dbConfigOk = true;
        }
        $conn = $cfg['conn'] instanceof mysqli ? $cfg['conn'] : null;
        $dbName = $cfg['dbname'];
    } catch (mysqli_sql_exception $e) {
        // db.php connects on include, so a connection failure surfaces here.
        // The driver message can contain host/user details, so it is logged
        // rather than printed.
        record('Database configuration', ST_PASS, 'includes/db.php loaded');
        record('Database connectivity', ST_FAIL,
            'Could not connect using the settings in includes/db.php. '
            . 'Check that the MySQL server is running, the socket path is correct, '
            . 'and the credentials and database name are valid.');
        $connectionAttempted = true;
    } catch (Throwable $e) {
        record('Database configuration', ST_FAIL,
            'includes/db.php could not be loaded (' . get_class($e) . '). Check the file for syntax errors.');
    }
} else {
    record('Database configuration', ST_FAIL, 'includes/db.php is missing.');
}

/* ------------------------------------------------- 4. Database contents */

$schemaChecked = false;
if ($connectionAttempted) {
    // Connectivity already reported above; schema could not be inspected.
    record('Schema verification', ST_SKIP, 'No database connection, so tables and columns were not verified.');
} elseif (!$dbConfigOk) {
    record('Database connectivity', ST_SKIP, 'Configuration incomplete, so no connection was attempted.');
} elseif (!$conn instanceof mysqli) {
    record('Database connectivity', ST_FAIL,
        'includes/db.php did not produce a mysqli connection. The server may be down or the socket path wrong.');
} else {
    try {
        $conn->query('SELECT 1');
        record('Database connectivity', ST_PASS, "connected to '{$dbName}' (server " . $conn->server_info . ')');

        // ---- required tables & columns (read-only, information_schema only)
        $expected = [
            'users'                  => ['id','full_name','email','password','role','department'],
            'proposals'              => ['id','user_id','title','status','start_date','end_date','report_path'],
            'proposal_messages'      => ['id','proposal_id','sender_id','message','created_at'],
            'proposal_financials'    => ['proposal_id','university_fund','registration_fund','sponsorship_fund','other_sources'],
            'proposal_guests'        => ['proposal_id','name','designation','address','contact_number','pan_number','reason_for_inviting'],
            'proposal_travel_accomm' => ['proposal_id','hotel_name_address','accommodation_days','who_arranges','mode','number_of_trips','who_provides','travel_address'],
            'proposal_budgets'       => ['proposal_id','category','type','quantity','cost_per_unit','total'],
            'proposal_sponsors'      => ['proposal_id','sponsor_category','amount_contributed','reward_perk','mode','about','benefits'],
            'preloaded_events'       => ['sl_no','event_date','event_month','activity','budget','university_contribution','convener'],
        ];
        $have = [];
        $st = $conn->prepare(
            'SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ?'
        );
        $st->bind_param('s', $dbName);
        $st->execute();
        $res = $st->get_result();
        while ($row = $res->fetch_assoc()) {
            $have[$row['TABLE_NAME']][] = $row['COLUMN_NAME'];
        }
        $st->close();

        $missingTables = [];
        $missingCols = [];
        foreach ($expected as $table => $cols) {
            if (!isset($have[$table])) {
                $missingTables[] = $table;
                continue;
            }
            foreach ($cols as $c) {
                if (!in_array($c, $have[$table], true)) {
                    $missingCols[] = "{$table}.{$c}";
                }
            }
        }
        if ($missingTables) {
            record('Required tables', ST_FAIL,
                'Missing table(s): ' . implode(', ', $missingTables) . '. Restore the schema before use.');
        } else {
            record('Required tables', ST_PASS, count($expected) . ' table(s) present');
        }
        if ($missingCols) {
            record('Required columns', ST_FAIL,
                "Missing column(s): " . implode(', ', $missingCols)
                . "\n    Run: php migrations/2026_08_28_align_schema_with_app.php");
        } else {
            record('Required columns', ST_PASS, 'all columns used by the application exist');
        }
        $schemaChecked = true;

        // ---- storage engine (transactions are relied upon)
        $st = $conn->prepare(
            "SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES
              WHERE TABLE_SCHEMA = ? AND TABLE_NAME IN ('proposals','proposal_messages')"
        );
        $st->bind_param('s', $dbName);
        $st->execute();
        $res = $st->get_result();
        $nonInno = [];
        while ($row = $res->fetch_assoc()) {
            if (strcasecmp((string) $row['ENGINE'], 'InnoDB') !== 0) {
                $nonInno[] = $row['TABLE_NAME'] . '=' . $row['ENGINE'];
            }
        }
        $st->close();
        if ($nonInno) {
            record('Transaction support', ST_FAIL,
                'Non-InnoDB table(s): ' . implode(', ', $nonInno)
                . '. Approve/reject/cancel rely on rollback and would silently half-apply.');
        } else {
            record('Transaction support', ST_PASS, 'proposals and proposal_messages are InnoDB');
        }

        // ---- statuses that the workflow cannot represent
        if (isset($have['proposals']) && in_array('status', $have['proposals'], true)) {
            $known = ['Pending', 'Approved', 'Rejected', 'Review', 'Cancelled', 'Rescheduled', 'Revision'];
            $placeholders = implode(',', array_fill(0, count($known), '?'));
            $sql = "SELECT status, COUNT(*) AS c FROM proposals
                     WHERE status IS NULL OR status = '' OR status NOT IN ({$placeholders})
                     GROUP BY status";
            $st = $conn->prepare($sql);
            $st->bind_param(str_repeat('s', count($known)), ...$known);
            $st->execute();
            $res = $st->get_result();
            $odd = [];
            while ($row = $res->fetch_assoc()) {
                $label = ($row['status'] === null || $row['status'] === '') ? '(empty)' : $row['status'];
                $odd[] = "{$label} x{$row['c']}";
            }
            $st->close();
            if ($odd) {
                record('Stored proposal statuses', ST_FAIL,
                    'Rows hold status values the workflow does not recognise: ' . implode(', ', $odd)
                    . '. These will not appear correctly on dashboards or the calendar.');
            } else {
                record('Stored proposal statuses', ST_PASS, 'all rows use recognised workflow statuses');
            }

            // 'Revision' is a legacy enum member no code path produces.
            $st = $conn->prepare("SELECT COUNT(*) AS c FROM proposals WHERE status = 'Revision'");
            $st->execute();
            $legacy = (int) $st->get_result()->fetch_assoc()['c'];
            $st->close();
            if ($legacy > 0) {
                record('Legacy "Revision" status', ST_WARN,
                    "{$legacy} row(s) use 'Revision', which no code path sets or clears. "
                    . "They will render with no matching dashboard filter. Decide whether they should be 'Review'.");
            } else {
                record('Legacy "Revision" status', ST_PASS, 'no rows use the unused legacy status');
            }
        }

        // ---- report_path rows whose file is gone
        if (isset($have['proposals']) && in_array('report_path', $have['proposals'], true)) {
            $res = $conn->query("SELECT id, report_path FROM proposals WHERE report_path IS NOT NULL AND report_path <> ''");
            $orphans = 0;
            $total = 0;
            while ($row = $res->fetch_assoc()) {
                $total++;
                if (!is_file($ROOT . '/' . ltrim((string) $row['report_path'], '/'))) {
                    $orphans++;
                }
            }
            if ($total === 0) {
                record('Report files', ST_PASS, 'no proposals reference a report yet');
            } elseif ($orphans > 0) {
                record('Report files', ST_WARN,
                    "{$orphans} of {$total} referenced report file(s) are missing from disk. "
                    . 'Downloads will 404. Check whether reports/ was cleared.');
            } else {
                record('Report files', ST_PASS, "all {$total} referenced report file(s) exist");
            }
        }
    } catch (Throwable $e) {
        // Never surface raw driver text.
        record('Database connectivity', ST_FAIL,
            'Could not query the database (' . get_class($e) . '). '
            . 'Verify the server is running and the credentials in includes/db.php are correct.');
    }
}
if (!$schemaChecked && $dbConfigOk && $conn instanceof mysqli === false) {
    record('Schema verification', ST_SKIP, 'No usable connection, so tables and columns were not verified.');
}

/* --------------------------------------------- 5. Storage directories */

// Permissions are inspected, never modified.
foreach (['reports' => 'generated event report PDFs'] as $dir => $why) {
    $path = $ROOT . '/' . $dir;
    if (!is_dir($path)) {
        record("Directory: {$dir}/", ST_WARN,
            "Does not exist ({$why}). api_save_report.php will try to create it on first upload.");
        continue;
    }
    if (!is_writable($path)) {
        record("Directory: {$dir}/", ST_FAIL,
            "Not writable by " . (get_current_user() ?: 'the current user')
            . ". Report uploads will fail. Grant write access to the web server user.");
        continue;
    }
    $perms = substr(sprintf('%o', fileperms($path)), -4);
    if ($perms === '0777') {
        record("Directory: {$dir}/", ST_WARN,
            "Writable, but mode {$perms} is world-writable. 0755 (or 0775 with the right group) is safer.");
    } else {
        record("Directory: {$dir}/", ST_PASS, "present and writable (mode {$perms})");
    }
}

/* ------------------------------------------------------------- output */

$counts = [ST_PASS => 0, ST_WARN => 0, ST_FAIL => 0, ST_SKIP => 0];
foreach ($results as $r) {
    $counts[$r['status']]++;
}
$exit = $counts[ST_FAIL] > 0 ? 1 : ($counts[ST_SKIP] > 0 ? 2 : 0);

if ($jsonMode) {
    echo json_encode([
        'summary'   => $counts,
        'exit_code' => $exit,
        'checks'    => $results,
        'limitations' => 'Environment and syntax only. Does not test workflow, authorization, '
                       . 'concurrency or integration behaviour.',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), "\n";
    exit($exit);
}

echo "SRM Event Connect - backend check\n";
echo str_repeat('=', 72), "\n";
foreach ($results as $r) {
    printf("[%s] %-28s %s\n", $r['status'], $r['check'], $r['detail']);
}
echo str_repeat('=', 72), "\n";
printf("%d passed, %d warning(s), %d failed, %d skipped\n",
    $counts[ST_PASS], $counts[ST_WARN], $counts[ST_FAIL], $counts[ST_SKIP]);
echo match ($exit) {
    1 => "Result: FAILURES CONFIRMED - see the FAIL lines above.\n",
    2 => "Result: INCOMPLETE - some required checks could not run (SKIP).\n",
    default => "Result: all required checks passed.\n",
};
echo "\nNote: this covers environment and syntax only. It does not verify workflow,\n";
echo "authorization, concurrency or integration behaviour.\n";
exit($exit);

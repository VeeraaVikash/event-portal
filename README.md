# SRM Event Connect

Event proposal and approval portal for the Department of Computing Technology,
SRM Institute of Science and Technology. Plain PHP + MySQL with Tailwind loaded
from a CDN. There is no build step and no package manager.

---

## Running locally

```bash
php -S localhost:8000
```

Then open <http://localhost:8000>. All paths are relative, so the site works
from any document root.

Database settings live in `includes/db.php`.

---

## Roles and workflow

Three roles, resolved from `users.role` and stored in `$_SESSION['role']`:

| Role | Dashboard | Can do |
|---|---|---|
| Convener (`faculty` / `Convener`) | `dashboard.php` | create, edit and resubmit own proposals; cancel; reschedule an approved event; upload the event report; chat while under review |
| HOD (`hod`) | `dashboard_hod.php` | approve, reject or request changes on proposals **from their own department**; chat while under review |
| Coordinator (`coordinator`) | `dashboard_coordinator.php` | read-only monitoring of their department, CSV export, pre-approved event import |

### Proposal states

`Pending` → `Approved` / `Rejected` / `Review`, plus `Cancelled` and
`Rescheduled`. (`Revision` exists in the database enum but no code path sets it;
it is retained only for backward compatibility.)

| From | Action | Role | To |
|---|---|---|---|
| — | create | convener | `Pending` |
| `Pending`, `Review`, `Rescheduled` | edit / resubmit | owner | unchanged |
| any except `Cancelled` | approve | HOD, same dept | `Approved` |
| any except `Cancelled` | reject *(reason required)* | HOD, same dept | `Rejected` |
| any except `Cancelled` | request changes *(reason required)* | HOD, same dept | `Review` |
| `Review` | chat message | owner or HOD, same dept | unchanged |
| any except `Cancelled` | cancel *(reason required)* | owner | `Cancelled` |
| `Approved` | reschedule *(reason + dates)* | owner | `Rescheduled` |

Rules enforced server-side:

- A cancelled proposal is frozen.
- Repeating the decision a proposal already carries is refused (409).
- Only `Approved` events can be rescheduled.
- `Approved`, `Rejected` and `Cancelled` proposals can no longer be edited.
- An HOD may still reverse an earlier decision from a **refreshed** dashboard.
- Only the owner may edit a proposal or upload its report; HODs and
  coordinators are limited to their own department.

### Concurrent decisions

The dashboards send `expected_status` — the status they were displaying — with
every action. If the proposal has changed since the page was loaded, the server
returns **409** and asks the user to refresh instead of silently overwriting
someone else's decision. Updates additionally use a compare-and-swap on
`status`, so two simultaneous writes cannot both apply.

---

## Health check

```bash
php scripts/check_backend.php          # human-readable
php scripts/check_backend.php --json   # machine-readable
```

Verifies the PHP version and required extensions, lints every application PHP
file, confirms configuration is present (without printing any secret), checks
database connectivity, required tables and columns, InnoDB (needed for
rollback), stored statuses the workflow cannot represent, referenced report
files, and the permissions on `reports/`.

**Exit codes**

| Code | Meaning |
|---|---|
| `0` | all required checks passed (warnings allowed) |
| `1` | at least one confirmed failure |
| `2` | a required check could not be completed, no confirmed failures |

**Safety.** CLI-only — it refuses HTTP access before loading any configuration.
It is strictly read-only: it never inserts, updates, deletes, migrates, repairs,
changes permissions, sends mail or triggers a business action. It never prints
passwords, tokens or connection strings, and never executes application files —
syntax checking uses `php -l` in a subprocess with arguments passed as a list,
so filenames are never shell-parsed.

**Limitations.** It covers environment and syntax only. It does **not** test
workflow, authorization, concurrency or integration behaviour, and a clean run
does not mean the backend is error-free. If process execution is unavailable the
syntax check reports `SKIP`, never `PASS`.

---

## Database migration

`migrations/2026_08_28_align_schema_with_app.php` brings a legacy database up to
what the application code expects. It was required because the deployed schema
predated the code: `proposals.status` lacked `Review`, `Cancelled` and
`Rescheduled`; `proposals.report_path` and `users.phone_number` did not exist;
and most guest, travel, budget and sponsor columns were absent. Under
`STRICT_TRANS_TABLES` those writes failed outright.

```bash
php migrations/2026_08_28_align_schema_with_app.php --dry-run   # preview
php migrations/2026_08_28_align_schema_with_app.php             # apply
```

**Compatibility.** Purely additive and idempotent. Nothing is renamed, dropped
or narrowed; every existing enum member (including the unused `Revision`) is
kept, and all legacy columns are left in place. Older code continues to work
against the migrated schema. Re-running it is a no-op.

**Deployment order**

1. Back up the database (see below).
2. Run the migration — it is additive, so it is safe to run before deploying code.
3. Deploy the application files.
4. Run `php scripts/check_backend.php` and confirm exit code `0`.

### Backup and restore

```bash
# back up
mysqldump --socket=/path/to/mysql.sock -u root \
  --databases event --routines --triggers --single-transaction \
  > backups/event_backup_$(date +%Y%m%d_%H%M%S).sql

# restore
mysql --socket=/path/to/mysql.sock -u root < backups/event_backup_<STAMP>.sql
```

Restoring the pre-migration dump is the supported rollback path.

> **Note on reversing the migration by hand:** narrowing `proposals.status` back
> to its original four values only works while no row uses `Review`, `Cancelled`
> or `Rescheduled`. Once the workflow has been used, those rows must be
> reassigned first or the `ALTER` fails with *Data truncated for column
> 'status'*. Restoring the dump avoids this entirely.

---

## Security notes

- All state-changing requests require a CSRF token. `fetch()` callers get the
  `X-CSRF-Token` header attached automatically by a small wrapper in
  `views/partials/head.php`; the two classic form POSTs (`proposal.php` and
  `api_upload_preloaded.php`) carry a hidden `csrf_token` field. `GET`
  endpoints (`api_proposal_details.php`, `api_export_proposals.php`) are
  read-only and unaffected.
- All queries use prepared statements.
- Report uploads are checked for the `%PDF-` signature and a 20 MB ceiling;
  the stored file is removed if the database write fails.
- Endpoint errors return a short opaque reference. The underlying exception goes
  to the PHP error log via `error_log()` — look for `[eventconnect][...][ref:…]`.
- `includes/db.php` holds plaintext credentials and is committed to the
  repository. That is acceptable for local development only; use environment
  variables or an untracked config file before deploying anywhere shared.

### Not addressed

- `login.php` and `signup.php` do not carry CSRF tokens. Authentication code
  was deliberately left untouched; login CSRF remains a known gap.
- There is no rate limiting on login or signup.

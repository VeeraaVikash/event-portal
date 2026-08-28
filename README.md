# SRM Event Connect

Event proposal and approval portal for the Department of Computing Technology,
SRM Institute of Science and Technology. Plain PHP + MySQL with Tailwind loaded
from a CDN. There is no build step and no package manager.

---

## Setup

Requirements: **PHP 8.1+** (with `mysqli`, `json`, `session`, `mbstring`,
`fileinfo`) and **MySQL 8.0+**. There is no build step and no package manager.

### 1. Configure database credentials

Credentials are **not** stored in the repository. `includes/db.php` reads them
from the environment first, then from `includes/config.local.php` — an
untracked file holding the values for your machine.

```bash
cp includes/config.local.example.php includes/config.local.php
```

Then edit `includes/config.local.php`:

```php
return [
    'host'     => 'localhost',
    'user'     => 'eventadmin',
    'password' => 'your_db_password',
    'dbname'   => 'event',
    'port'     => 3306,
    'socket'   => null,   // unix socket path, or null to use TCP
];
```

Every value can also come from the environment, which takes precedence over the
file: `DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME`, `DB_PORT`, `DB_SOCKET`.
Use these for shared or production deployments and leave `config.local.php`
absent.

### 2. Create the database and user

```sql
CREATE DATABASE event CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'eventadmin'@'localhost' IDENTIFIED BY 'your_db_password';
GRANT ALL PRIVILEGES ON event.* TO 'eventadmin'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Create the tables

On a **new, empty** database, load the full schema:

```bash
mysql -u eventadmin -p event < migrations/schema.sql
```

That file creates all nine tables in dependency order and already includes
everything the migration adds, so no migration is needed afterwards. It uses
`CREATE TABLE IF NOT EXISTS`, so re-running it is a no-op.

On a **pre-existing legacy** database, run the migration instead — see
[Database migration](#database-migration).

### 4. Run

```bash
php -S localhost:8000
```

Then open <http://localhost:8000>. All paths are relative, so the site works
from any document root.

### 5. Verify

```bash
php scripts/check_backend.php
```

Exit code `0` means the environment is set up correctly.

---

## Setting up on Windows

The application itself is portable — only the environment differs. Two routes:

### Route A — XAMPP (simplest)

1. Install [XAMPP](https://www.apachefriends.org/) (PHP 8.1+). This bundles
   Apache, PHP and MariaDB, which is drop-in compatible here.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Copy the project into `C:\xampp\htdocs\eventconnect`.
4. Create the database at <http://localhost/phpmyadmin> — add a database named
   `event` with collation `utf8mb4_unicode_ci`, then create the user and grant
   as in step 2 above. With `event` selected, open the **Import** tab and load
   `migrations\schema.sql` to create the tables.
5. Create `includes\config.local.php` from the example. On Windows there is no
   unix socket, so **`socket` must be `null`** and the connection goes over TCP:

   ```php
   return [
       'host'     => '127.0.0.1',
       'user'     => 'eventadmin',
       'password' => 'your_db_password',
       'dbname'   => 'event',
       'port'     => 3306,
       'socket'   => null,
   ];
   ```

   XAMPP's default MariaDB root account has an empty password; if you use it
   directly, set `'user' => 'root'` and `'password' => ''`.
6. Check the install from a terminal in the project folder:

   ```powershell
   C:\xampp\php\php.exe scripts\check_backend.php
   ```

   All checks should pass. If you loaded `schema.sql` in step 4 there is no
   migration to run.

7. Open <http://localhost/eventconnect>.

### Route B — standalone PHP + MySQL (no Apache)

1. Install PHP for Windows from [windows.php.net](https://windows.php.net/download/)
   (pick a **Thread Safe** x64 build) and unzip to `C:\php`.
2. Add `C:\php` to `PATH`, then enable the required extensions in `C:\php\php.ini`
   by uncommenting these lines (remove the leading `;`):

   ```ini
   extension_dir = "ext"
   extension=mysqli
   extension=mbstring
   extension=fileinfo
   extension=openssl
   ```

3. Install [MySQL Community Server](https://dev.mysql.com/downloads/mysql/) and
   create the database and user as in step 2 above, then load the schema:

   ```powershell
   mysql -h 127.0.0.1 -u eventadmin -p event < migrations\schema.sql
   ```

4. Create `includes\config.local.php` with `'socket' => null` as shown in Route A.
5. Run the built-in server from the project folder:

   ```powershell
   php -S localhost:8000
   php scripts\check_backend.php
   ```

### Windows-specific notes

- **`socket` must be `null`.** Unix socket paths like
  `/Users/.../mysql.sock` do not exist on Windows; leaving one in place causes
  a connection failure. Connect over TCP to `127.0.0.1:3306` instead.
- **Prefer `127.0.0.1` over `localhost`.** On some Windows setups `localhost`
  resolves to IPv6 `::1` while MySQL only listens on IPv4, which shows up as
  "connection refused".
- **Set environment variables** (alternative to the config file) in PowerShell:

  ```powershell
  $env:DB_HOST = "127.0.0.1"
  $env:DB_USER = "eventadmin"
  $env:DB_PASSWORD = "your_db_password"
  $env:DB_NAME = "event"
  php -S localhost:8000
  ```

  These last only for that shell session. For a permanent value use
  `[Environment]::SetEnvironmentVariable("DB_NAME", "event", "User")`.
- **`reports/` must be writable** by the account running PHP. Under IIS or
  Apache-as-a-service that is the service account, not your login.
- **Backups** use the same `mysqldump`, without the socket flag:

  ```powershell
  mysqldump -h 127.0.0.1 -u root -p --databases event --routines --triggers `
    --single-transaction > backups\event_backup.sql
  ```

- **Line endings.** The repository has no `.gitattributes`; if Git converts PHP
  files to CRLF it is harmless, but `git config core.autocrlf false` keeps the
  working tree byte-identical to what is committed.

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

## Deploying on IIS (college server)

A campus server is a different threat model from `localhost`. Work through this
in order; steps 1–6 are not optional.

### 1. Install the PHP handler

IIS does not run PHP on its own.

1. Server Manager → **Add Roles and Features** → Web Server (IIS), and under
   Application Development enable **CGI**.
2. Install PHP 8.1+ **Non-Thread-Safe** x64 to `C:\php` (non-thread-safe is the
   correct build for FastCGI; the thread-safe build is for Apache).
3. In `C:\php\php.ini` uncomment `extension_dir = "ext"` and
   `extension=mysqli`, `mbstring`, `fileinfo`, `openssl`.
4. IIS Manager → server node → **Handler Mappings** → *Add Module Mapping*:

   | Field | Value |
   |---|---|
   | Request path | `*.php` |
   | Module | `FastCgiModule` |
   | Executable | `C:\php\php-cgi.exe` |
   | Name | `PHP_via_FastCGI` |

5. **Default Document** → add `index.php`.

### 2. Deploy the files

Copy the project to e.g. `C:\inetpub\wwwroot\eventconnect`, then in IIS Manager
add an Application or Site pointing at it.

**Do not copy `.git`.** It contains the full history, including the database
credentials from before they were moved out of `includes/db.php`. Export a
clean tree instead:

```powershell
git archive --format=zip --output=eventconnect.zip HEAD
```

`web.config` in the site root is picked up automatically. It disables directory
browsing, blocks `reports`, `includes`, `migrations`, `scripts`, `backups`,
`.git` and `.claude` from the web, removes the `X-Powered-By` banner, and sets
`X-Content-Type-Options`, `X-Frame-Options` and `Referrer-Policy`.

### 3. Give the app pool write access to `reports/`

Report uploads are written by the IIS worker process, not by your login.

1. IIS Manager → Application Pools → note the pool name (e.g. `eventconnect`).
2. Right-click the `reports` folder → Properties → Security → Edit → Add.
3. Enter `IIS AppPool\eventconnect`, click **Check Names**, grant
   **Modify**.

Grant this on `reports\` only. The rest of the site should stay read-only to
the worker — that way a compromised upload cannot rewrite application code.

### 4. Database

Create the database and user on the college MySQL server, load
`migrations\schema.sql`, then create `includes\config.local.php` with
`'socket' => null` and `'host' => '127.0.0.1'` (or the DB server's hostname).

**Rotate the password.** The old one (`eventpass`) is in git history and must
not be reused on a shared server. Grant only what the app needs — it never
issues DDL at runtime:

```sql
GRANT SELECT, INSERT, UPDATE, DELETE ON event.* TO 'eventadmin'@'localhost';
```

Run the migration under a temporarily broader grant, then drop back to this.

### 5. Turn off error display

`php.ini` on the server:

```ini
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = "C:\inetpub\logs\php_errors.log"
expose_php = Off
session.cookie_httponly = 1
session.cookie_secure = 1     ; requires HTTPS (step 6)
session.cookie_samesite = Lax
```

`display_errors = On` prints file paths, queries and connection details onto
the page. The app already routes exceptions to `error_log()` with a reference
code, which is what users should see instead.

### 6. HTTPS

Bind a certificate to the site (your IT department will normally issue one for
the campus domain), then uncomment the **Force HTTPS** rewrite rule in
`web.config`. It needs the
[URL Rewrite module](https://www.iis.net/downloads/microsoft/url-rewrite).

Sessions travel in a cookie. Without TLS, anyone on the campus network can read
a logged-in HOD's session cookie off the wire and act as them.

Once HTTPS is confirmed working, uncomment the
`Strict-Transport-Security` header too.

### 7. Verify

```powershell
C:\php\php.exe scripts\check_backend.php
```

Then confirm from a browser **on another machine** that these are all blocked:

| URL | Expected |
|---|---|
| `https://host/eventconnect/reports/` | 404 — no directory listing |
| `https://host/eventconnect/reports/Report_PRO_0001_1776167030.pdf` | 404 |
| `https://host/eventconnect/includes/db.php` | 404 |
| `https://host/eventconnect/includes/config.local.php` | 404 |
| `https://host/eventconnect/.git/config` | 404 |
| `https://host/eventconnect/migrations/schema.sql` | 404 |

If any of these returns content, stop and fix `web.config` before letting
anyone use the site.

### Known gaps to raise with your IT department

These are unresolved in the application and matter more on a shared server:

- **No rate limiting on login.** Passwords can be guessed at network speed.
  Mitigate with IIS **Dynamic IP Restrictions**, or put the site behind the
  campus SSO/VPN.
- **No CSRF token on `login.php` / `signup.php`.** Every other state-changing
  endpoint has one; authentication was deliberately left untouched.
- **The three demo accounts** (`faculty@srm.edu`, `coordinator@srm.edu`,
  `hod@srm.edu`) must be deleted or given strong unique passwords before the
  site is reachable by anyone else. They are well-known credentials.
- **Report PDFs already in the repository** (`reports/*.pdf`, 25 files) ship
  with the code. If they contain real event data, remove them from the deployed
  copy and from git.

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

Only needed for a **pre-existing legacy** database. A database created from
`migrations/schema.sql` is already current — skip this section.

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
- Report PDFs are served only through `download_report.php`, which requires a
  session and applies the same permission rule as `api_proposal_details.php`
  (the owning convener, or an HOD/coordinator in the same department). The
  stored path comes from the database rather than the request, and is confirmed
  to resolve inside `reports/`. `web.config` blocks direct access to the folder,
  because report filenames are predictable (`Report_PRO_0007_<unixtime>.pdf`)
  and a directly reachable `reports/` would let anyone enumerate them without
  logging in. **On any server other than localhost, serving `reports/` directly
  is a data leak.**
- Endpoint errors return a short opaque reference. The underlying exception goes
  to the PHP error log via `error_log()` — look for `[eventconnect][...][ref:…]`.
- No credentials are committed. `includes/db.php` reads them from the
  environment (`DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME`, `DB_PORT`,
  `DB_SOCKET`) or from `includes/config.local.php`, which is listed in
  `.gitignore`. Only `includes/config.local.example.php`, containing
  placeholders, is tracked.

### Not addressed

- `login.php` and `signup.php` do not carry CSRF tokens. Authentication code
  was deliberately left untouched; login CSRF remains a known gap.
- There is no rate limiting on login or signup.

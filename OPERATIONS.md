# Marell Academy — Operations Guide

Project: Jenga Web School OS (Marell Academy instance)
Owner:  Jenga Web Technologies
Stack:  Laravel 13, PHP 8.5, MariaDB, Blade + TailwindCSS, Safaricom Daraja (M-Pesa STK Push), Africa's Talking SMS

This document is the single reference for running, backing up, and deploying the system.
Last updated: CP-31.

---

## 1. Layout

- Repo root: the Laravel project. `app/`, `routes/`, `resources/`, `database/`.
- `deploy/` — supervisor configs and Termux worker script.
- `stress_test.php` (gitignored) — 71-check regression suite. Runs against the live dev DB.
- `$HOME/db_backups/` — SQL dumps taken before each schema-changing CP.
- Database name: `marell` (dev). App DB user: `marell@localhost`.

---

## 2. Environment keys that matter

| Key | Purpose | Notes |
|---|---|---|
| `APP_ENV` | `local` in dev, `production` on VPS | CP-26 guard refuses to boot if `production` + debug |
| `APP_DEBUG` | `true` in dev, must be `false` in production | |
| `APP_TIMEZONE` / `APP_TIMEZONE` | Kenya ops uses Africa/Nairobi | Set in `config/app.php` |
| `DB_*` | MariaDB connection | App user has ALL on `marell`.* only |
| `DB_TIMEZONE` | `+03:00` | Set in `config/database.php`, overridable |
| `CURRENT_TERM`, `CURRENT_YEAR` | Fee term for new payments (CP-30) | Update at start of each term |
| `MPESA_ENV` | `sandbox` or `production` | |
| `MPESA_SKIP_STK` | Demo only — skip real Safaricom | Fail-closed if production + this on |
| `DEMO_MODE` | Demo auto-complete | Same fail-closed rule |
| `MPESA_KILL_SWITCH` | `1` stops all new payment initiation | Callbacks still process |
| `MPESA_CALLBACK_SECRET` | Secret path segment for the callback (CP-02) | Empty => all callbacks 404 |
| `MPESA_CALLBACK_LEGACY_ENABLED` | Old callback path during cutover | Set to false after Daraja is repointed |
| `TRUSTED_PROXIES` | Comma-separated proxy IPs | Empty = trust no `X-Forwarded-For` (correct without a proxy) |

---

## 3. Workers and scheduler

Two background processes must run for money to actually credit and for scheduled tasks to fire.

### Termux (dev)

    nohup bash deploy/termux-worker.sh >> storage/logs/worker.log 2>&1 &

Starts two loops:
- `php artisan queue:work --queue=mpesa,default --tries=3 --timeout=90 --sleep=3 --max-time=3600`
- `php artisan schedule:run` every 60s

PID files: `storage/app/worker.pid`, `storage/app/scheduler.pid`.
Stop by killing both, or `pkill -f "queue:work --queue=mpesa,default"`.

Termux has no init system — restart manually after a phone reboot.

### Ubuntu VPS (supervisor)

    sudo apt-get install -y supervisor
    sudo cp deploy/jenga-worker.conf    /etc/supervisor/conf.d/
    sudo cp deploy/jenga-scheduler.conf /etc/supervisor/conf.d/
    sudo supervisorctl reread
    sudo supervisorctl update
    sudo supervisorctl status

Adjust `/var/www/marell`, `user=www-data`, `numprocs` in the conf files if your install differs.

### Scheduled tasks (from bootstrap/app.php)

- 07:00 EAT — defaulter report to Director
- 08:00 EAT — fee autopilot
- 06:55 EAT — apply late fines
- 02:00 EAT — nightly checkpoint

---

## 3b. Database users

The app connects as a **non-privileged user**, not as the schema owner.

| User | Purpose | Grants |
|---|---|---|
| `marell_app` | Application runtime | `SELECT, INSERT, UPDATE, DELETE ON marell.*` |
| `marell` | Schema owner. Migrations and DDL only. | `ALL PRIVILEGES ON marell.*` |

`.env` points the app at `marell_app`. An SQL injection or RCE in the app
cannot DROP, ALTER, or CREATE tables. Blast radius is data manipulation only,
and every mutation is already guarded at the app layer (`lockForUpdate`,
unique indexes, kill switch).

**Running migrations:** `php artisan migrate` needs DDL. Temporarily set
`DB_USERNAME=marell` and the `marell` password in `.env`, run the migration,
then swap back to `marell_app`. Do not leave the app running as `marell`.

**Rotating the app user password:** as root,
`ALTER USER 'marell_app'@'localhost' IDENTIFIED BY '<new>'; FLUSH PRIVILEGES;`
then update `DB_PASSWORD` in `.env` and restart the workers.
## 4. Backups and restore

### Making a backup

    pw=$(grep '^DB_PASSWORD=' .env | cut -d= -f2- | tr -d '"')
    mysqldump -umarell -p"$pw" marell > $HOME/db_backups/marell_$(date +%Y%m%d_%H%M).sql

Keep the most recent one; older ones can be pruned.

### Restoring

    mysql -uroot -e "DROP DATABASE marell; CREATE DATABASE marell CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    mysql -uroot marell < $HOME/db_backups/marell_YYYYMMDD_HHMM.sql

Then restart the worker so it reconnects.

### Schema parity check

Confirms a fresh `php artisan migrate` produces the same schema as live:

    mysql -uroot -e "DROP DATABASE IF EXISTS marell_scratch; CREATE DATABASE marell_scratch CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL PRIVILEGES ON marell_scratch.* TO 'marell'@'localhost'; FLUSH PRIVILEGES;"
    DB_DATABASE=marell_scratch php artisan migrate --force
    mysqldump -uroot --no-data --skip-comments --compact marell | grep -v AUTO_INCREMENT= > /tmp/live.sql
    mysqldump -uroot --no-data --skip-comments --compact marell_scratch | grep -v AUTO_INCREMENT= > /tmp/fresh.sql
    diff /tmp/live.sql /tmp/fresh.sql
    mysql -uroot -e "DROP DATABASE marell_scratch;"

Empty diff (modulo column ordering) = parity.

---

## 5. Tests

    php artisan test                                    # PHPUnit suites
    vendor/bin/phpunit --filter=Cp27PayInitRateLimitTest  # one CP suite
    php stress_test.php                                 # 71-check regression, live DB
    php -l app/Path/To/File.php                         # syntax check any edited file

PHPUnit runs against SQLite `:memory:` and does not have the full schema
— source-level assertions are the realistic form there. Stress and the
schema parity check run against the real MariaDB.

---

## 6. Kill switch and safety controls

| Control | Env | Effect |
|---|---|---|
| Kill switch | `MPESA_KILL_SWITCH=1` | Blocks new payment initiation and wallet top-up. Callbacks still process so real money is recorded. |
| Demo fail-closed | — | If `MPESA_ENV!=sandbox` and either demo flag is on, payments are refused. |
| Production debug guard | — | Refuses to boot if `APP_ENV=production` and `APP_DEBUG=true`. Use `APP_ENV=staging` for legitimate prod debugging. |
| Duplicate receipt | — | DB unique indexes on `payments.transaction_code`, `wallet_transactions.mpesa_receipt`. App-level checks are fast paths. |
| Amount mismatch | — | `initiated_amount` is preserved. Any difference from callback amount holds the row as `under_reconciliation`. |

---

## 7. Logs

- Application: `storage/logs/laravel.log` (rotated by Laravel; PII redacted by `LogRedactor`).
- Worker: `storage/logs/worker.log`.
- Scheduler: `storage/logs/scheduler.log`.
- On VPS: also `/var/log/marell/*.log` via supervisor stdout.

Never commit `storage/logs/*` — already gitignored.

---

## 8. Owner action checklists

### Before running a real payment for the first time

1. `.env` on the VPS: `APP_ENV=production`, `APP_DEBUG=false`.
2. `MPESA_ENV=production`, real Safaricom credentials in place.
3. `MPESA_CALLBACK_SECRET` set to a random 32-hex string.
4. `MPESA_CALLBACK_URL` points at `https://<host>/api/mpesa/cb/<secret>`.
5. Daraja dashboard callback URL matches.
6. `TRUSTED_PROXIES` set if behind Cloudflare / load balancer / reverse proxy.
7. Queue worker and scheduler running (supervisor status shows both).
8. DB backup taken within the last 24 hours.
9. Test payment executed and confirmed on the success page and in the DB.
10. `MPESA_CALLBACK_LEGACY_ENABLED=false` once the new path is confirmed working.
11. Kill switch off (`MPESA_KILL_SWITCH=0` or unset).

### At the start of each school term

1. Update `CURRENT_TERM` and `CURRENT_YEAR` in `.env`.
2. `php artisan config:clear`.
3. Restart the worker so the new config takes effect.

### After a phone / server reboot

1. Restart the queue worker and scheduler (see section 3).
2. Confirm the scheduler tick: `tail -1 storage/logs/scheduler.log` should be recent.
3. Confirm a worker is running: `ps aux | grep queue:work | grep -v grep`.

---

## 9. Emergency procedures

### Payment flow is misbehaving

Set `MPESA_KILL_SWITCH=1` in `.env`, `php artisan config:clear`, restart the worker.
Callbacks already in flight still record; new initiations are refused with a friendly message.

### Something is wrong with a specific payment

1. Look at the row in `payments` or `wallet_transactions` by `checkout_request_id`.
2. Check its `status` and `notes` — `under_reconciliation` rows carry the reason.
3. Check `jobs` and `failed_jobs` tables if a callback seems stuck.
4. Check `storage/logs/worker.log` for that `checkout_request_id` (PII is redacted but the ID and status flow are visible).

### Rollback a CP

Every CP is one commit. `git revert <hash>` and, if the CP had a migration,
`php artisan migrate:rollback --step=1` (see the migration's `down()` — several
are guarded no-ops by design; the guard text explains why).

---

## 10. What is not in this document

- Multi-tenancy, UI redesign, premium features — Phase 3+ items.
- Accountant-facing ledger semantics — separate work, needs an accountant's review.
- The evidence tables (`mpesa_events`, `mpesa_allocations`) — Phase 2.
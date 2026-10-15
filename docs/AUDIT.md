# SKULI360 — PHASE 0 AUDIT
Date: 2026-10-14 · Instance: Marell Academy · Baseline commit: 7447b26 · Owner: Jenga Web Technologies

## 1. Environment — verified vs claimed

| Item | Spec/handover claim | Repo reality | Status |
|---|---|---|---|
| PHP | 8.5 | 8.5.1 (NTS, OPcache) | OK |
| Laravel | 13 | v13.32.0 (2026-09-15) | OK |
| Database (dev) | MariaDB | MySQL driver, DB=MariaDB (unverified version) | partial |
| Timezone | Africa/Nairobi | Africa/Nairobi | OK |
| Locale | en | en | OK |
| APP_NAME | Skuli360 (§1) | "Jenga Web School OS" | MISMATCH |
| Dev URL | localhost:8001 | localhost:8000 | minor |
| Cache/Session/Queue | not specified | all `database` | noted |
| APP_DEBUG | must be false in prod | ENABLED (local) | OK for dev |
| storage:link | not stated | NOT LINKED | noted |
| Spatie Permissions | not in spec | 8.3.0 installed | see §6 risk |

## 2. Inventory

- Models: 44 (handover claimed 5)
- Migrations: 47 (handover claimed ~10)
- Blade views: 70+ across 20+ dirs
- Components: 9 (handover claimed 10 — double-counted x-card)
- Layouts: 5 (public, admin, dos, bursar, teacher)
- Routes: 276 (baseline saved at docs/baseline/routes.json)
- Tests: 33 Feature + 2 Unit (handover implied none)
- Test DB: sqlite :memory: (phpunit.xml)

## 3. Test reality — the headline

Baseline: 2 failed, 209 passed, 607 assertions, 41.57s.

Critical finding: before this audit, **no test in the repo touched the database**. `ExampleTest.php` had `RefreshDatabase` commented out. Every Cp-prefixed test is source-grep or HTTP-without-persistence — a tripwire, not a behaviour test. Spec §10.2 money-path behaviour suite does not exist. See L30, L31.

Failing tests:
1. `ExampleTest` — `GET /` non-200 (default scaffold, cosmetic)
2. `Cp11PayPagePrivacyTest::test_blade_does_not_prefill_phone` — stale grep against migrated `<x-input>` (security property intact; test is source-grep only)

## 4. Payment write path

`Payment::create` sites (all inside `DB::transaction` with Student `lockForUpdate`):
- `PaymentController.php:103` — public `/pay` initiate
- `FinanceController.php:75` — staff manual record
- `BursarController.php:76` — bursar manual record
- `BursarController.php:293` — bulk CSV import

`Payment::generateReceiptNo()` (`app/Models/Payment.php:33`)
- Format: `REC-YYYY-NNNNN` (spec L2 says `RCP-YYYY-NNNNN`; repo wins per §0.4)
- Uses `SELECT MAX(...) FOR UPDATE` on payments range
- `receipt_no` has unique index (`2026_09_20_133818`)
- **No caller catches unique-index QueryException** — see L2-revised

Receipt verify path: `VerifyController` (public verify route, checksum-less, throttled?)
Receipt PDF: `PaymentController@receipt` (signed), `BursarController@receipt`

## 5. Wallet write path

`WalletTransaction::create` sites:
- `WalletController.php:62` — parent top-up (`PaymentSafety::paymentsAllowed()` gate, rate-limited 5/900s, `initiated_amount` set)
- `CanteenItemController.php:99` — canteen checkout (locked, stock+limit+balance checks)
- `Student.php:92` — `topUpWallet()` model method (NO row lock inside)
- `Student.php:115` — `spendWallet()` model method (NO row lock inside)

Callers:
- `WalletController@adminLoad` — calls `topUpWallet()` with no surrounding transaction → RACE (see L33)
- `CanteenItemController@checkout` — has `$locked` row
- `parentTopUp` — pending txn written before M-Pesa, no lock

Deprecated: `canteenSell` returns HTTP 410, logs, enforces replacement (CP-18).

## 6. M-Pesa callback

Controller: `MpesaCallbackController` — secret check, IP allowlist, shape check, dispatches `ProcessMpesaCallback` on `mpesa` queue.
Job: `ProcessMpesaCallback` — far more careful than spec L3 implied:
- `lockForUpdate()` in every DB write transaction
- Duplicate `transaction_code` check with DB unique index as load-bearing guarantee (CP-29)
- Amount-mismatch → `under_reconciliation` with notes
- No `MpesaReceiptNumber` → `under_reconciliation`
- SMS after commit (correct — failure does not roll back credit)
- Does NOT persist raw callback to a dedicated `payment_callbacks` table (spec L3 desired)

## 7. Findings L29+ (renumbered; §9 L1–L28 remain)

L29  Duplicate migrations: `2026_09_22_073910` and `2026_09_22_073955` both `add_topup_to_wallet_type_enum`. Both ran. Leave.
L30  MySQL-only DDL (`DB::statement("ALTER TABLE ... MODIFY ...")`) in migrations blocks sqlite tests. `phpunit.xml` uses sqlite. Any `RefreshDatabase` test crashes. Spec §10.1 "separate testing DB" wants MariaDB.
L31  Money-path behaviour suite §10.2 does not exist. 33 test files are source-grep tripwires.
L32  No StudentFactory; only UserFactory exists. Spec §10.1 violated.
L33  `WalletController@adminLoad` → `Student::topUpWallet()` with no transaction. Concurrent admin loads can lose a credit or produce inconsistent `balance_after`. Fix in Phase 1: wrap in `DB::transaction` + `lockForUpdate`, OR make `topUpWallet`/`spendWallet` acquire their own lock.
L34  L2 revised: `generateReceiptNo()` relies on gap-lock `FOR UPDATE` semantics; safe under MariaDB REPEATABLE READ, unsafe under READ COMMITTED. No caller catches unique-index violation.
L35  Spec L2 format `RCP-YYYY-NNNNN` vs repo `REC-YYYY-NNNNN`. Repo wins; spec wording stale.
L36  Ad-hoc scripts at repo root: `demo_seed.php` (git-tracked, contains `.demo_seed_confirm` production bypass guard), `stress_test.php` (untracked), `audit.php` (untracked). Classify and quarantine.
L37  `composer audit`: 2 advisories on `league/commonmark` (medium PKSA-m2dq-1fhr-29b1, high PKSA-m4t9-vsgq-8khn). Unreachable from app code — no Mailables, no markdown blades, no `Str::markdown` calls. Risk accepted in writing. Re-audit when a Mailable is introduced.
L38  Logo mark contains diagonal texture inside tile — spec §5.1/§5.6 forbid stripes. Phase 3 fix.
L39  `canteenSell` deprecated endpoint still routed, returns 410. OK.
L40  `CanteenItemController@checkout` SMS wrapped in empty `catch (\Throwable $e) {}` — swallows all errors silently. Spec L6 spirit. Fix in Phase 1.

## 8. Open questions §17 — status from repo evidence

1. RCP000000 origin: `demo_seed.php:122` (demo artifact, not prod path). CLOSED.
2. parent_login/parent_verify end-to-end: NOT VERIFIED (needs fake-SMS test).
3. library/* auth: NOT VERIFIED (route middleware needs reading).
4. dos/teacher-subjects writers: NOT VERIFIED.
5. Frozen pages with emoji redesign: deferred to Phase 4 per spec.
6. Payment states/columns: from migration `2026_09_20_133818` + `2026_10_09_180000` + `2026_10_09_190000` — CLOSED.
7. Multi-step transaction around payment+receipt+allocation: YES — every Payment::create is inside a DB::transaction, and receipt is generated inside it. CLOSED.
8. Jobs and schedule: NOT VERIFIED.
9. SMS provider return stored: NOT VERIFIED.
10. Daraja env/security: config/services.mpesa + callback_legacy_enabled flag exist. Values not printed. PARTIAL.

## 9. Verified-green components

- Theme system: `partials/theme.blade.php`, single source, CSS vars + Tailwind config. Matches spec §5.2.
- Component library: 9 components match handover §6.
- Auth: staff `/login`, parent OTP `/parent/login`, guard `/login` (name collision L10 open).
- Public marketing site: full sweep done prior to Phase 0.
- Frozen preserved byte-for-byte: signed URLs, form field names, route names.

## 10. Phase 0 gate status

- [x] Versions reported
- [x] route:list baseline saved (59,888 bytes JSON)
- [x] Models, migrations, tests inventoried
- [x] `php artisan test` run recorded (2F/209P)
- [x] `composer audit` run, risk-acceptance written
- [x] Payment/wallet/receipt write sites mapped
- [x] M-Pesa callback read in full
- [x] Wallet + canteen checkout read
- [x] DEAD_CODE.md written (separate file)
- [x] §17 questions answered where repo evidence allows; remainder flagged NOT VERIFIED
- [ ] Owner review
- [ ] Tag phase-0-done

## 11. Feature classification (spec §14 Phase 0 mandatory)

Legend: IV = implemented-verified; P = partial; B = broken; BO = backend-only; U = unused; NB = not built.

| Feature | Class | Evidence |
|---|---|---|
| Staff login `/login` | IV | `LoginController`, live route line 19 |
| Parent OTP `/parent/login`+`/parent/verify` | P | routes exist; end-to-end unverified (Q2) |
| Guard login `/login` | IV | `SecurityGuardController`; name collision L10 |
| Public pay GET `/pay` | IV | `PaymentController@show`, masked student, Cp11 source-grep |
| Public pay POST `/pay` | IV | `PaymentController@initiate`, throttle 10,1 |
| Pay wait/check/success/receipt (signed) | IV | signed middleware, frozen |
| Receipt verify `/verify-receipt/{no}` | IV | `VerifyController` |
| M-Pesa secure callback | IV | `MpesaCallbackController` secret+shape check |
| M-Pesa legacy callback | P | env-gated; allowlist L41 |
| `ProcessMpesaCallback` job | IV | locks, dup-check, amount-match, SMS-after-commit |
| Raw callback persistence table | NB | spec L3; see L45 |
| Payment manual record (staff/bursar/bulk) | IV | `FinanceController`,`BursarController` transactions |
| Receipt number generation | P | gap-lock reliance; L34 |
| Wallet parent top-up | P | rate-limited; no row lock on pending txn |
| Wallet admin load | P | race L33 |
| Canteen checkout | IV | `CanteenItemController@checkout` locked + checks |
| Canteen items CRUD | P | hard-delete L43; emoji L42 |
| `canteenSell` tombstone | IV | returns 410 (L39) |
| Fee autopilot | B | handover §11 / spec L6-L8 |
| Live collection poll | P | L16 |
| Prediction thresholds | P | L23 |
| Library | U/P | auth unverified (Q3) |
| Visitors / Gate / Pickup | U/P | not read |
| Results / report cards | U/P | not read |
| Boarding module | NB | Phase 5 |
| Transport module | NB | Phase 6 |
| `school_modules` registry | NB | §7.3 |
| Settings admin page | NB | spec L18 |
| Audit log | P | table+model exist; wiring unverified |
| Service worker | P | L15 |
| Spatie permissions | P | usage in routes vs menus unverified |

## 12. §17 open questions — closure

Q1 RCP000000 — CLOSED (`demo_seed.php:122` demo artifact)
Q2 parent OTP end-to-end — OPEN (Phase 1 test)
Q3 library auth — OPEN (read middleware)
Q4 dos/teacher-subjects writers — OPEN
Q5 frozen emoji redesign — CLOSED (Phase 4 only)
Q6 payment states/columns — CLOSED
Q7 payment multi-step transaction — CLOSED
Q8 jobs and schedule — OPEN (grep app/Jobs + schedule:list)
Q9 SMS provider return — OPEN (read SmsService)
Q10 Daraja env — PARTIAL (config keys exist; values not printed)

## 13. Findings added this step (L41-L45)

L41  `MpesaCallbackController@ipAllowed` returns true when allowlist empty. Safe only if `callback_secret` set. Verify env in Phase 1.
L42  `CanteenItemController` JSON responses embed check/cross emoji. Violates §2.11/§5.1.
L43  `CanteenItemController@destroy` hard-deletes, no soft-delete, no audit entry.
L44  SMS templates hardcode "MARELL ACADEMY" / "MARELL WALLET" / "MARELL CANTEEN" / "MARELL ALERT" in `ProcessMpesaCallback`, `CanteenItemController`, `WalletController`. Violates §1.
L45  No `payment_callbacks` table. Spec L3 wants raw-payload persistence before dispatch.

## 14. Incident log

### 2026-10-14 — dev DB students table wiped by runaway tinker command

Cause: an automated `php artisan tinker` command intended to run against the test DB was issued without `--env=testing`. It ran against the dev DB `marell` and executed `Student::query()->delete()`, cascading through every FK-linked table.

Blast radius (dev DB `marell`):
- Wiped via cascade: students, payments, wallet_transactions, attendances, results, fee_escalations, pickup_logs, book_loans
- Unaffected: users, timetables, canteen_items, expenses, visitors, admissions, settings

Recovery:
- Located drill snapshot `~/db_backups/marell_drill_20261010_012121.sql` (Oct 10, post-migrations)
- Safety-dumped damaged state to `~/db_backups/marell_pre_restore_20261014_085107.sql`
- Restored from drill snapshot; verified counts (students 139, payments 236, wallet_transactions 242, attendances 560)

Residual loss: none detected; drill snapshot matched all survivor tables 1:1 before restore.

Rule change (self-imposed):
- Every DB-writing command must state the target env/DB in the command message and include `--env=testing` or `--database=marell_test` when the target is the test DB.
- No destructive DB operation in the same message as any other operation.
- Any command whose exit code can be masked by a pipeline must be run without the pipeline, or the pipeline's exit code checked separately.

## 15. Findings surfaced during Phase 1A

L41  `MpesaCallbackController@ipAllowed` returns true when allowlist empty. Safe only if callback_secret set.
L42  `CanteenItemController` JSON responses embed check/cross emoji. Violates §2.11/§5.1.
L43  `CanteenItemController@destroy` hard-deletes, no soft-delete, no audit entry.
L44  SMS templates hardcode "MARELL ACADEMY" / "MARELL WALLET" / "MARELL CANTEEN" / "MARELL ALERT" in ProcessMpesaCallback, CanteenItemController, WalletController. Violates §1.
L45  No `payment_callbacks` table. Spec L3 wants raw-payload persistence before dispatch.
L46  Existing git tags v1.0-demo-ready, v1.0.1-demo-seeded do not match spec §8 format (vMAJOR.MINOR.PATCH).
L47  FIXED 92e6451 — public home called `User::role("teacher")->count()`, which throws RoleDoesNotExist on fresh DBs. Replaced with whereHas.
L48  `public/pay-success.blade.php:58` — `URL::signedRoute('verify.receipt', [...])` throws if `$payment->receipt_no` is null. Production completed payments always have one, but no guard exists. Latent 500. Fix in Phase 1D.

## 16. Phase 1A outcomes

- Test DB moved sqlite :memory: → MariaDB marell_test (spec §10.1)
- StudentFactory, PaymentFactory, WalletTransactionFactory created (spec §10.1)
- WalletTransaction: HasFactory enabled
- ExampleTest: self-contained with RefreshDatabase, passing
- Cp11: rewritten as HTTP-render (real behavioural proof, not source-grep)
- Cp15b: NEW — first money-path behaviour test hitting DB + signed URLs + rendering
- Test suite: 211 → 215 passing, 0 failing
- L5: verified satisfied in code, now covered by Cp15b
- Incident 2026-10-14 (dev DB wiped, restored) documented in §14

## 17. Phase 1D progress — L12 closed

L12 status: **verified, not fixed.** The library routes were already correct:
  - `library.*` (staff) — `auth` + `role:dos|principal|bursar|teacher`
  - `parent.library` — `parent.auth` (OTP session)
  - `library.public` (`/library-catalog`) — intentionally public marketing catalog

Coverage added in `tests/Feature/LibraryAuthTest.php` (6 tests).

**Deferred to Phase 2:** the `module:library` middleware required by §7.3. That middleware cannot be applied until the module registry exists. Registered here so it is not lost.

END OF AUDIT.

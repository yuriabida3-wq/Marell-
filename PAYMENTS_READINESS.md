# Payments Readiness Assessment

Assessed at CP-32 (23df0f0). Updated at CP-34 for the DB-user and restore-drill closures.
Scope: whether the system can be trusted with **real parent money** through a live Safaricom Paybill.

## 1. Summary verdict

**NOT READY for real payments today.**

Several code-level and DB-level guards are in place. But four of the brief's
Go/No-Go gates are operations items that have not been performed, and three
are owner decisions outside the code base. Real money should not flow until
the items in sections 3 and 4 are closed.

Verdict will flip to **READY WITH CONDITIONS** when sections 3 and 4 below
are all signed off.

## 2. Go / No-Go gate table

| Gate | Status | Evidence |
|---|---|---|
| Public exposure closed and tested | **MET** | CP-01 (2becf95), CP-01b (76d07ea), CP-02 (22bb011), CP-09 (14f7817), CP-10 (2c7a0d2), CP-11 (64c8e40), CP-27 (ca851d9) |
| Demo fails closed and tested | **MET** | CP-15 (add83eb) — PaymentSafety::configIsDangerous() refuses non-sandbox with demo flags |
| Evidence log live | **MET** | DB unique indexes on payments.transaction_code / checkout_request_id / receipt_no and wallet_transactions.mpesa_receipt / checkout_request_id (CP-24, 90de9f4) |
| Anomalies held for staff | **MET** | CP-14a (9c3b23e) under_reconciliation state, CP-14b (1de743e) amount mismatch |
| Stale-pending sweeper running | **OPEN** | Phase 2 item; no CP yet. Rows can sit pending indefinitely if a callback never arrives. |
| Workers supervised and monitored | **PARTIAL** | CP-19 (71b5494) — supervisor configs and Termux script exist. No external monitoring / alerting on worker death. |
| Kill switch tested | **MET** | CP-15 (add83eb); manual verification path documented in OPERATIONS.md §9 |
| Non-privileged DB user | **MET** | OPS-DBUSER 2026-10-10. App runs as `marell_app@localhost` with only SELECT, INSERT, UPDATE, DELETE on `marell`.*. DDL refused at DB layer (CREATE returns ERROR 1142). `marell` user retained for migrations only. See OPERATIONS.md §3b. |
| Restore witnessed | **MET** | Restore drill run 2026-10-10 (CP-33). Backup \`marell_drill_20261010_012121.sql\` restored into \`marell_restore_drill\`; all seven core tables matched row-for-row (students 139, users 21, payments 236, wallet_transactions 242, visitors 17, staff_attendances 660, canteen_items 10). Script: \`deploy/restore_drill.sh\`. |
| Finance MFA | **OPEN** | No MFA on any login path. Passwords + PINs only. |
| Debug off in production | **DEPLOY-TIME CHECK** | CP-26 (a1575be) — app refuses to boot if APP_ENV=production and APP_DEBUG=true. Operator must still set APP_DEBUG=false. |
| Credentials rotated | **PARTIAL** | App DB password rotated CP-16b (no commit — operational). Safaricom Daraja credentials are not in this DB; rotation is an owner action. |
| Custody and data-protection positions documented | **OWNER ACTION** | Not a code item. |
| Accountant review of fee and wallet rules | **OWNER ACTION** | Not a code item. |
| Safaricom onboarding confirmed in writing | **OWNER ACTION** | Not a code item. |
| Concurrency tests on MariaDB | **PARTIAL** | MariaDB 13.0.2 is the dev engine; tests run there. No load/concurrency test at production scale. |

## 3. Owner actions outstanding (before real money)

These are not code items. They require the owner's decision or an external party.

1. ~~**Non-privileged DB user** — provision a `marell_app` user with SELECT/INSERT/UPDATE/DELETE only, no ALTER/CREATE/DROP. Update `.env`.~~ **DONE 2026-10-10.** `marell_app` created with four privileges. DDL rejected. `.env` swapped and app verified. See OPERATIONS.md §3b.
2. ~~**Restore drill** — pick the most recent `$HOME/db_backups/marell_*.sql`, restore into a scratch DB, verify row counts match, delete scratch. Record the drill date.~~ **DONE 2026-10-10.** Script at \`deploy/restore_drill.sh\`. All seven core tables matched. Re-run monthly or after any schema change as an ongoing check.
3. **Safaricom Daraja account onboarding** — obtain live shortcode, passkey, consumer key/secret for the school's Paybill. Coordinate with the school's Paybill owner.
4. **Callback URL cutover** — once live credentials are in place: set MPESA_CALLBACK_SECRET, set MPESA_CALLBACK_URL to the new secure path, update the Daraja dashboard, verify a sandbox callback reaches it, then set MPESA_CALLBACK_LEGACY_ENABLED=false.
5. **Finance MFA** — decide the mechanism (TOTP via a phone app, hardware key, or an SMS second factor). Not implemented today.
6. **Accountant review** — walk a chartered accountant through: fee obligation model, wallet credit/debit rules, `under_reconciliation` handling, mismatch policy. Get a written sign-off on the money-movement rules.
7. **Data protection & custody** — a lawyer to confirm: who owns parent wallet balances (school vs Jenga), retention policy per Kenyan law, DPA registration if applicable, privacy notice for parents.
8. **VPS deploy** — provision Ubuntu VPS, install PHP 8.5 + MariaDB, configure supervisor with `deploy/jenga-*.conf`, set `.env` from the pre-payment checklist in `OPERATIONS.md` §8, run schema parity check.

## 4. Residual risks accepted

Items the owner has decided not to close, with the reasoning.

| Risk | Why accepted |
|---|---|
| Historical timestamps skewed pre-CP-07 | Demo DB only. If promoted to prod, a backfill is a separate deliberate CP. |
| Wallet seed rows indistinguishable from real by prefix (D2) | Demo DB only. Production starts clean. |
| No stale-pending sweeper | Rows that never receive a callback stay pending. Visible to staff only via the finance view. Bounded by the operator's willingness to look. Phase 2 closes it. |
| No worker monitoring / alerting | Supervisor restarts on crash. Detection of a silently-stuck worker is manual. Acceptable at current scale. |
| SMS is plaintext OTP over Africa's Talking | Inherent to SMS. Mitigated by 5-minute validity and per-phone limits. |

## 5. Recommended go-live sequence

Ordered steps from today to first real payment.

1. Section 3 items 1 and 2 are DONE (2026-10-10). Begin at item 3. Re-run the drill monthly or after schema changes.
2. Provision the VPS (item 8). Do not point live money at it yet.
3. Sandbox test on VPS: full payment flow end to end, callback received on the new secret path, wallet credited, SMS sent, receipt verifiable.
4. Kill switch test: set MPESA_KILL_SWITCH=1, attempt a payment, confirm refusal, unset.
5. Concurrency sanity: fire 5 parallel sandbox callbacks with the same receipt, confirm exactly one credit lands and the other four are held.
6. Section 3 items 3–5 (Safaricom onboarding, cutover, MFA decision).
7. Section 3 items 6–7 (accountant review, legal review). Do not skip.
8. **Go-live day:** real credentials in `.env`, callback URL updated, APP_DEBUG=false, first payment with the school's own phone, verifications as in OPERATIONS.md §8.
9. Hold a 24-hour review of the first real payments before opening to all parents.

## 6. What this document is not

- Not a legal opinion.
- Not a substitute for the accountant review.
- Not a security audit by an external party.
- Not a load test. Volume behaviour under 1,000 concurrent payments is unknown.

Prepared as part of CP-32 (23df0f0). Regenerate whenever material changes land.
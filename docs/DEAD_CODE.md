# SKULI360 — DEAD CODE REGISTER (Phase 0)
Date: 2026-10-14 · Baseline commit: 7447b26

Policy (spec §2.7, §18): never delete code whose purpose has not been read in full. Nothing here is deleted without per-item owner approval. This file is a register — read-only reference for Phase 1+ cleanup.

## Confirmed dead (safe to remove on approval)

### Routes
- `routes/web.php` — duplicate bursar route group, lines ~141–162 vs ~163–184. Handover and grep both confirmed identical. Second group wins. Removal requires `route:list` diff proof + approval (spec L11).

### Controllers
- `App\Http\Controllers\LoginHubController` — no route. Never invoked. (handover §4 / spec L24)
- `App\Http\Controllers\UnifiedAuthController` — no route. Never invoked.

### Views
- `resources/views/auth/unified-login.blade.php` — git-tracked, unreferenced.
- `resources/views/public/login-hub.blade.php` — unreferenced.
- Note: `resources/views/auth/login.blade.php` IS LIVE — do not remove.

### Endpoints (kept deliberately, not dead)
- `POST /canteen/sell` — `WalletController@canteenSell` returns HTTP 410, logs calls. **Intentional tombstone** for the deprecated free-text endpoint (CP-18). Keep until telemetry shows zero hits for 30 days.

### Repo-root ad-hoc scripts (see L36)
- `demo_seed.php` — git-tracked. Contains production bypass guard `.demo_seed_confirm`. Recommend: move to `database/seeders/DemoSnapshotSeeder.php` OR add `if (env('APP_ENV') !== 'local') die();` unconditionally. Requires approval.
- `stress_test.php` — untracked, at repo root. Recommend move to `tests/Manual/` or delete. Untracked so no history loss.
- `audit.php` — untracked, at repo root. Same recommendation.

## Duplicate / redundant (not dead but flag)

- Migrations `2026_09_22_073910` and `2026_09_22_073955` — both `add_topup_to_wallet_type_enum`. Both already ran. Do NOT delete (spec §3). Recorded as L29.

## Deferred (Phase 4 decision)

- Frozen pay-flow pages with residual content emoji (if any).
- Parent-portal legacy views — some may be superseded by newer parent pages. Requires page-by-page read in Phase 4.

## Dead — after L9 (2026-10-15)

- `App\Http\Controllers\QrDemoController::quickchartUrl()` — was the only server-side
  quickchart.io URL builder. After L9 the QR hub view calls `QrService::svgDataUrl()`
  directly. Method left in place per §2.7. Line 24 comment retained.

## NOT dead — leave alone

- `resources/views/auth/login.blade.php` (LIVE)
- All signed-URL routes and their controllers
- `Payment`, `WalletTransaction`, `Student` models — actively written by controllers
- `MpesaCallbackController` legacy endpoint (`MPESA_CALLBACK_LEGACY_ENABLED`) — env-controlled, not dead
- Any currently passing test

## Removal log

(Nothing removed yet. Each Phase 1+ removal gets an entry here: date, item, commit hash, approver.)

END OF REGISTER.

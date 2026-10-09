<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-01: canteen, gate-console staff-scan, and orphan principal routes
 * must refuse unauthenticated callers.
 *
 * These tests assert the HTTP gate only. They intentionally do NOT hit
 * the database — the middleware must reject the request before any
 * controller or DB query runs.
 */
class Cp01RouteProtectionTest extends TestCase
{
    public function test_canteen_sell_rejects_unauthenticated(): void
    {
        $this->post('/canteen/sell', [])->assertStatus(302);
        $this->postJson('/canteen/sell', [])->assertStatus(401);
    }

    public function test_canteen_checkout_rejects_unauthenticated(): void
    {
        $this->post('/canteen/checkout', [])->assertStatus(302);
        $this->postJson('/canteen/checkout', [])->assertStatus(401);
    }

    public function test_canteen_lookup_rejects_unauthenticated(): void
    {
        $this->post('/canteen/lookup', [])->assertStatus(302);
        $this->postJson('/canteen/lookup', [])->assertStatus(401);
    }

    public function test_canteen_index_rejects_unauthenticated(): void
    {
        $this->get('/canteen')->assertStatus(302);
    }

    public function test_canteen_logs_rejects_unauthenticated(): void
    {
        $this->get('/canteen/logs')->assertStatus(302);
    }

    public function test_canteen_menu_rejects_unauthenticated(): void
    {
        $this->get('/canteen/menu')->assertStatus(302);
    }

    public function test_gate_console_staff_scan_rejects_unauthenticated(): void
    {
        $this->post('/gate/console/staff-scan', [])->assertStatus(302);
        $this->postJson('/gate/console/staff-scan', [])->assertStatus(401);
    }

    public function test_principal_staff_attendance_rejects_unauthenticated(): void
    {
        $this->get('/principal/staff-attendance')->assertStatus(302);
    }

    public function test_principal_staff_qrcodes_rejects_unauthenticated(): void
    {
        $this->get('/principal/staff-qrcodes')->assertStatus(302);
    }

    public function test_principal_canteen_report_rejects_unauthenticated(): void
    {
        $this->get('/principal/canteen-report')->assertStatus(302);
    }
}

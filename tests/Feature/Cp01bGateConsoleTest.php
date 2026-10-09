<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * CP-01b: guards authenticate via session('guard_id'), not Auth.
 * The gate/console group must accept guard.or.staff, not bare auth.
 */
class Cp01bGateConsoleTest extends TestCase
{
    public function test_gate_console_index_redirects_unauthenticated(): void
    {
        $this->get('/gate/console')->assertStatus(302);
    }

    public function test_gate_console_visitor_in_rejects_unauthenticated(): void
    {
        $this->post('/gate/console/visitor', [])->assertStatus(302);
        $this->postJson('/gate/console/visitor', [])->assertStatus(401);
    }

    public function test_gate_console_visitor_out_rejects_unauthenticated(): void
    {
        $this->post('/gate/console/visitor-out', [])->assertStatus(302);
        $this->postJson('/gate/console/visitor-out', [])->assertStatus(401);
    }

    public function test_gate_console_routes_use_guard_or_staff_middleware(): void
    {
        $names = [
            'gate.console.index',
            'gate.console.stats',
            'gate.console.recent',
            'gate.console.inside',
            'gate.console.visitor-in',
            'gate.console.visitor-out',
            'gate.console.staff-scan',
        ];

        foreach ($names as $name) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route, "Route {$name} not registered");

            $mw = $route->gatherMiddleware();
            $this->assertContains(
                'guard.or.staff',
                $mw,
                "Route {$name} missing guard.or.staff middleware"
            );
            $this->assertNotContains(
                'auth',
                $mw,
                "Route {$name} still carries bare auth middleware"
            );
        }
    }
}

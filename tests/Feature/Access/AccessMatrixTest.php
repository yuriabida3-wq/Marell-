<?php

namespace Tests\Feature\Access;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * L20 discovery: NOT a gate test.
 *
 * Iterates GET routes × actors, records the outcome, writes
 * docs/ACCESS_MATRIX_BASELINE.md. Never asserts.
 *
 * Red flags are the deliverable:
 *   - auth-protected route returns 200 to guest
 *   - wrong-role actor returns 200 where 'role:' middleware present
 *   - any route returns 500 (broken)
 */
class AccessMatrixTest extends TestCase
{
    use RefreshDatabase;

    private const SKIP_PATTERNS = [
        '#logout#',
        '#destroy#',
        '#delete#',
        '#mpesa/cb#',
        '#mpesa/callback#',
        '#parent/verify$#',
        '#parent/send-otp#',
        '#login$#',    // POSTs, but just in case a GET slips through
    ];

    public function test_write_access_matrix_baseline(): void
    {
        foreach (['principal', 'dos', 'bursar', 'teacher', 'security'] as $r) {
            Role::firstOrCreate(['name' => $r]);
        }

        $actors = [
            'guest'     => null,
            'principal' => $this->makeUser('principal'),
            'dos'       => $this->makeUser('dos'),
            'bursar'    => $this->makeUser('bursar'),
            'teacher'   => $this->makeUser('teacher'),
            'guard'     => $this->makeUser('security'),
        ];

        // Seed minimal student so {id}/{adm} routes don't hard-fail before middleware runs.
        Student::factory()->create();

        $routes = $this->getRoutes();
        $report = [
            '# ACCESS MATRIX BASELINE',
            '',
            'Generated: ' . now()->toIso8601String(),
            'Actors: guest, principal, dos, bursar, teacher, guard',
            'Method: GET only. Destructive / callback / OTP routes skipped.',
            'Status codes only — no assertions.',
            '',
            'Legend: `2xx` allowed · `302` redirect (login) · `403` denied · `404` not found · `5xx` error',
            '',
            '| Route | URL | ' . implode(' | ', array_keys($actors)) . ' |',
            '|---|---|' . str_repeat('---|', count($actors)),
        ];

        $redFlags = [];
        $errors    = [];

        foreach ($routes as $r) {
            $cells = [];
            foreach ($actors as $label => $user) {
                if ($user) {
                    $this->actingAs($user);
                } else {
                    auth()->logout();
                }

                try {
                    $resp   = $this->get($r['url']);
                    $status = $resp->getStatusCode();
                } catch (\Throwable $e) {
                    $status = 'ex';
                    $errors[] = "{$r['url']} ({$label}): " . $e->getMessage();
                }

                $cells[] = $this->shortStatus($status);

                if ($status >= 500) {
                    $redFlags[] = "`5xx` on **{$r['url']}** as {$label} — investigate";
                }
                if ($label === 'guest' && $status >= 200 && $status < 300 && $r['auth_protected']) {
                    $redFlags[] = "**Guest 200 on auth-protected** `{$r['url']}` ({$r['name']})";
                }
                if ($label !== 'guest' && $status >= 200 && $status < 300 && !empty($r['allowed_roles']) && !in_array($label, $r['allowed_roles'], true)) {
                    $allowed = implode('|', $r['allowed_roles']);
                    $redFlags[] = "**{$label} 200 but only [{$allowed}] allowed** `{$r['url']}` ({$r['name']})";
                }
            }

            $report[] = "| `{$r['name']}` | `{$r['url']}` | " . implode(' | ', $cells) . ' |';
        }

        $report[] = '';
        $report[] = '## Red flags (' . count($redFlags) . ')';
        $report[] = '';
        $report[] = $redFlags ? implode("\n", array_map(fn ($f) => "- {$f}", $redFlags)) : '_none_';
        $report[] = '';
        $report[] = '## Errors (' . count($errors) . ')';
        $report[] = '';
        $report[] = $errors ? implode("\n", array_map(fn ($e) => "- {$e}", $errors)) : '_none_';

        file_put_contents(base_path('docs/ACCESS_MATRIX_BASELINE.md'), implode("\n", $report));

        $this->assertTrue(true, 'baseline written');
    }

    private function makeUser(string $role): User
    {
        $u = User::factory()->create(['role_label' => $role]);
        $u->assignRole($role);
        return $u;
    }

    private function getRoutes(): array
    {
        $out = [];
        foreach (Route::getRoutes() as $route) {
            if (!in_array('GET', $route->methods(), true)) {
                continue;
            }
            $uri = $route->uri();
            if ($uri === '/') {
                $url = '/';
            } else {
                // Fill simple params with placeholder IDs so route-model-binding
                // returns 404 rather than throwing.
                $url = '/' . preg_replace('/\{[^}]+\??\}/', '999999', $uri);
            }

            foreach (self::SKIP_PATTERNS as $pat) {
                if (preg_match($pat, $uri)) {
                    continue 2;
                }
            }

            $middleware = $route->gatherMiddleware();
            $allowedRoles = [];
            foreach ($middleware as $m) {
                if (is_string($m) && str_starts_with($m, 'role:')) {
                    $allowedRoles = array_merge($allowedRoles, explode('|', substr($m, 5)));
                }
            }
            $out[] = [
                'name'            => $route->getName() ?? '(unnamed)',
                'url'             => $url,
                'auth_protected'  => (bool) array_intersect($middleware, ['auth', 'parent.auth', 'guard.or.staff']),
                'allowed_roles'   => array_values(array_unique($allowedRoles)),
            ];
        }
        return $out;
    }

    private function shortStatus($s): string
    {
        if ($s === 'ex')          return 'ex';
        if ($s >= 500)            return '5xx';
        if ($s >= 400)            return (string) $s;
        if ($s >= 300)            return (string) $s;
        if ($s >= 200 && $s < 300) return '2xx';
        return (string) $s;
    }
}

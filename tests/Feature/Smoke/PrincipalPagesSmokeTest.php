<?php

namespace Tests\Feature\Smoke;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Broad sweep: every GET route under /principal must not 5xx.
 *
 * For routes with {id} params we fill with "1"; a 404 is acceptable (the
 * record may not exist) — only 5xx and error markers fail the sweep.
 *
 * Skipped:
 *   - logout
 *   - any route with {receipt_no} (needs a real receipt)
 */
class PrincipalPagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    private const ERROR_MARKERS = [
        'Whoops', 'SQLSTATE[', 'Fatal error:', 'Parse error:',
        'Call to undefined', 'Undefined variable', 'Undefined property',
        'Undefined array', 'Stack trace:', 'Base table or view not found',
        'Trying to access array offset', '@extends(', '@section(', '@yield(',
    ];

    public function test_every_principal_get_page_does_not_500(): void
    {
        foreach (['principal','dos','bursar','teacher','security'] as $r) {
            Role::firstOrCreate(['name' => $r]);
        }
        $principal = User::factory()->create(['role_label' => 'principal']);
        $principal->assignRole('principal');
        $this->actingAs($principal);

        Student::factory()->create();

        $failures = [];
        $checked  = 0;
        $skipped  = 0;

        foreach (Route::getRoutes() as $route) {
            if (!in_array('GET', $route->methods(), true)) continue;
            $uri = $route->uri();
            if (!str_starts_with($uri, 'principal/') && $uri !== 'principal') continue;
            if (str_contains($uri, 'logout')) { $skipped++; continue; }
            if (str_contains($uri, '{receipt_no}')) { $skipped++; continue; }
            if (str_contains($uri, 'export')) { $skipped++; continue; }

            $url = '/' . preg_replace('/\{[^}]+\??\}/', '1', $uri);
            $checked++;

            try {
                $resp = $this->get($url);
                $status = $resp->getStatusCode();
            } catch (\Throwable $e) {
                $failures[] = "{$url} → exception: " . $e->getMessage();
                continue;
            }

            if ($status >= 500) {
                $failures[] = "{$url} → {$status}";
                continue;
            }

            if ($status >= 200 && $status < 300) {
                $body = $resp->getContent();
                $contentType = (string) $resp->headers->get('Content-Type');
                $isJson = str_contains($contentType, 'json');
                $isFile = str_contains($contentType, 'octet-stream') || str_contains($contentType, 'spreadsheet') || str_contains($contentType, 'csv');

                if (!$isJson && !$isFile && strlen($body) < 200) {
                    $failures[] = "{$url} → 200 (HTML) but only " . strlen($body) . " bytes";
                    continue;
                }
                foreach (self::ERROR_MARKERS as $marker) {
                    if (str_contains($body, $marker)) {
                        $failures[] = "{$url} → 200 but contains '{$marker}'";
                        break;
                    }
                }
            }
        }

        $summary = "Checked {$checked} routes, skipped {$skipped}.";
        if ($failures) {
            $summary .= "\n\nFailures (" . count($failures) . "):\n" . implode("\n", $failures);
        }
        $this->assertEmpty($failures, $summary);
    }
}

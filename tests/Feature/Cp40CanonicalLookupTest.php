<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-40: single canonical canteen lookup; canteen.menu retired to a redirect.
 */
class Cp40CanonicalLookupTest extends TestCase
{
    private function routes(): string
    {
        return (string) file_get_contents(base_path('routes/web.php'));
    }

    private function canteenCtrl(): string
    {
        return (string) file_get_contents(base_path('app/Http/Controllers/CanteenItemController.php'));
    }

    private function walletCtrl(): string
    {
        return (string) file_get_contents(base_path('app/Http/Controllers/WalletController.php'));
    }

    public function test_shadowed_lookup_route_is_gone(): void
    {
        $src = $this->routes();
        $this->assertStringNotContainsString(
            "Route::post('/lookup',  [App\\Http\\Controllers\\CanteenItemController::class, 'lookup'])",
            $src
        );
    }

    public function test_canteen_item_controller_lookup_method_gone(): void
    {
        $this->assertStringNotContainsString('public function lookup(', $this->canteenCtrl());
    }

    public function test_canteen_item_controller_menu_method_gone(): void
    {
        $this->assertStringNotContainsString('public function menu(', $this->canteenCtrl());
    }

    public function test_menu_route_is_a_redirect(): void
    {
        $src = $this->routes();
        $this->assertStringContainsString(
            "Route::get('/menu',     fn () => redirect()->route('canteen.index'))->name('menu')",
            $src
        );
    }

    public function test_canteen_menu_blade_deleted(): void
    {
        $this->assertFileDoesNotExist(base_path('resources/views/canteen/menu.blade.php'));
    }

    public function test_canteen_lookup_returns_enriched_fields(): void
    {
        $src = $this->walletCtrl();
        $this->assertStringContainsString("'daily_limit'        => (float) \$student->daily_limit", $src);
        $this->assertStringContainsString("'blocked_items'      => \$student->blocked_items ?? []", $src);
        $this->assertStringContainsString("'blocked_categories' => \$student->blocked_categories ?? []", $src);
    }

    public function test_canteen_view_consumes_blocked_and_limit(): void
    {
        $src = (string) file_get_contents(base_path('resources/views/canteen/index.blade.php'));
        $this->assertStringContainsString('d.student.blocked_items', $src);
        $this->assertStringContainsString('d.student.daily_limit', $src);
    }

    public function test_no_code_references_item_lookup_route(): void
    {
        $needles = ['canteen.item-lookup'];
        $paths = [
            'app',
            'routes',
            'resources/views/canteen/index.blade.php',
            'resources/views/canteen/items.blade.php',
        ];
        foreach ($paths as $p) {
            $full = base_path($p);
            if (is_dir($full)) {
                $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($full));
                foreach ($iterator as $f) {
                    if ($f->isFile() && preg_match('/\.(php|blade\.php)$/', $f->getFilename())) {
                        $src = (string) file_get_contents($f->getPathname());
                        foreach ($needles as $n) {
                            $this->assertStringNotContainsString($n, $src, "{$n} referenced in {$f->getPathname()}");
                        }
                    }
                }
            } elseif (is_file($full)) {
                $src = (string) file_get_contents($full);
                foreach ($needles as $n) {
                    $this->assertStringNotContainsString($n, $src, "{$n} referenced in {$p}");
                }
            }
        }
    }
}
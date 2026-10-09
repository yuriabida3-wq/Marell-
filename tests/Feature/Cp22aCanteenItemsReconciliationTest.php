<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-22a: canteen_items reconciliation.
 */
class Cp22aCanteenItemsReconciliationTest extends TestCase
{
    private function guarded(): string
    {
        return (string) file_get_contents(base_path('database/migrations/2026_09_22_072644_create_canteen_items_table.php'));
    }

    public function test_failing_migration_now_has_guard(): void
    {
        $src = $this->guarded();
        $this->assertStringContainsString("if (Schema::hasTable('canteen_items'))", $src);
        $this->assertStringContainsString('return;', $src);
    }

    public function test_failing_migration_down_is_noop(): void
    {
        $src = $this->guarded();
        // down() must not drop the table — earlier migration owns it.
        $this->assertStringNotContainsString("Schema::dropIfExists('canteen_items')", $src);
    }

    public function test_widening_migration_exists(): void
    {
        $path = base_path('database/migrations/2026_10_09_210000_extend_canteen_items_columns.php');
        $this->assertFileExists($path);
    }

    public function test_widening_migration_widens_columns(): void
    {
        $src = (string) file_get_contents(base_path('database/migrations/2026_10_09_210000_extend_canteen_items_columns.php'));
        $this->assertStringContainsString('MODIFY COLUMN name VARCHAR(255)', $src);
        $this->assertStringContainsString('MODIFY COLUMN category VARCHAR(255)', $src);
        $this->assertStringContainsString('MODIFY COLUMN price DECIMAL(10,2)', $src);
    }

    public function test_widening_migration_adds_missing_columns_guarded(): void
    {
        $src = (string) file_get_contents(base_path('database/migrations/2026_10_09_210000_extend_canteen_items_columns.php'));
        $this->assertStringContainsString("hasColumn('canteen_items', 'stock')", $src);
        $this->assertStringContainsString("hasColumn('canteen_items', 'blockable')", $src);
        $this->assertStringContainsString("hasColumn('canteen_items', 'image')", $src);
    }

    public function test_widening_migration_down_is_noop(): void
    {
        $src = (string) file_get_contents(base_path('database/migrations/2026_10_09_210000_extend_canteen_items_columns.php'));
        // No DROP / MODIFY-narrow in down().
        $this->assertStringNotContainsString('DROP COLUMN', $src);
    }
}
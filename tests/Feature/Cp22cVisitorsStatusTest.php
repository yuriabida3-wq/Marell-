<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-22c: visitors.status enum widened to match live.
 */
class Cp22cVisitorsStatusTest extends TestCase
{
    private function mig(): string
    {
        $p = base_path('database/migrations/2026_10_09_220000_widen_visitors_status_enum.php');
        $this->assertFileExists($p);
        return (string) file_get_contents($p);
    }

    public function test_migration_exists(): void
    {
        $this->assertFileExists(base_path('database/migrations/2026_10_09_220000_widen_visitors_status_enum.php'));
    }

    public function test_migration_widens_enum_to_include_inside(): void
    {
        $src = $this->mig();
        $this->assertStringContainsString(
            "ENUM('expected','inside','checked_in','checked_out')",
            $src
        );
    }

    public function test_migration_sets_default_to_inside(): void
    {
        $src = $this->mig();
        $this->assertStringContainsString("NOT NULL DEFAULT 'inside'", $src);
    }

    public function test_migration_guards_hasTable(): void
    {
        $src = $this->mig();
        $this->assertStringContainsString("Schema::hasTable('visitors')", $src);
    }

    public function test_down_is_noop(): void
    {
        $src = $this->mig();
        $this->assertStringNotContainsString('DROP COLUMN', $src);
        $this->assertStringNotContainsString('MODIFY COLUMN status ENUM(\'expected\',\'checked_in\'', $src);
    }
}
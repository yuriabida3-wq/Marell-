<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-33: restore drill script exists and does the right steps.
 */
class Cp33RestoreDrillScriptTest extends TestCase
{
    private function sh(): string
    {
        $p = base_path('deploy/restore_drill.sh');
        $this->assertFileExists($p);
        return (string) file_get_contents($p);
    }

    public function test_script_exists(): void
    {
        $this->assertFileExists(base_path('deploy/restore_drill.sh'));
    }

    public function test_backs_up_live_database(): void
    {
        $src = $this->sh();
        $this->assertStringContainsString('mysqldump', $src);
        $this->assertStringContainsString('marell_drill_', $src);
    }

    public function test_uses_scratch_database(): void
    {
        $src = $this->sh();
        $this->assertStringContainsString('marell_restore_drill', $src);
        $this->assertStringContainsString('CREATE DATABASE', $src);
        $this->assertStringContainsString('DROP DATABASE', $src);
    }

    public function test_compares_core_tables(): void
    {
        $src = $this->sh();
        foreach ([
            'students',
            'users',
            'payments',
            'wallet_transactions',
            'visitors',
            'staff_attendances',
            'canteen_items',
        ] as $t) {
            $this->assertStringContainsString($t, $src, "Missing table in drill: {$t}");
        }
    }

    public function test_has_pass_fail_verdict(): void
    {
        $src = $this->sh();
        $this->assertStringContainsString('RESTORE DRILL: PASS', $src);
        $this->assertStringContainsString('RESTORE DRILL: FAIL', $src);
    }

    public function test_does_not_write_to_live_database(): void
    {
        $src = $this->sh();
        // Sanity check: no INSERT / UPDATE / DELETE against `marell`.
        $this->assertStringNotContainsString('DELETE FROM', $src);
        $this->assertStringNotContainsString('INSERT INTO', $src);
        $this->assertStringNotContainsString('UPDATE ', $src);
    }
}
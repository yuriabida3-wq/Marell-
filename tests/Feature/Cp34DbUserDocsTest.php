<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-34 (OPS-DBUSER): documents the non-privileged DB user setup.
 */
class Cp34DbUserDocsTest extends TestCase
{
    private function ops(): string
    {
        return (string) file_get_contents(base_path('OPERATIONS.md'));
    }

    private function readiness(): string
    {
        return (string) file_get_contents(base_path('PAYMENTS_READINESS.md'));
    }

    public function test_operations_documents_db_user_section(): void
    {
        $this->assertStringContainsString('## 3b. Database users', $this->ops());
    }

    public function test_operations_names_both_users(): void
    {
        $src = $this->ops();
        $this->assertStringContainsString('marell_app', $src);
        $this->assertStringContainsString('Schema owner', $src);
    }

    public function test_operations_lists_the_four_privileges(): void
    {
        $this->assertStringContainsString(
            'SELECT, INSERT, UPDATE, DELETE ON marell.*',
            $this->ops()
        );
    }

    public function test_operations_documents_migration_workflow(): void
    {
        $src = $this->ops();
        $this->assertStringContainsString('php artisan migrate', $src);
        $this->assertStringContainsString('Temporarily set', $src);
    }

    public function test_readiness_gate_is_met(): void
    {
        $src = $this->readiness();
        $this->assertStringContainsString(
            '| Non-privileged DB user | **MET**',
            $src
        );
        $this->assertStringContainsString('marell_app@localhost', $src);
    }

    public function test_readiness_item_1_is_done(): void
    {
        $this->assertStringContainsString(
            'DONE 2026-10-10',
            $this->readiness()
        );
    }
}
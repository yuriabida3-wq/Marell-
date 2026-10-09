<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-31: operations runbook exists and documents the critical procedures.
 */
class Cp31OperationsDocTest extends TestCase
{
    private function doc(): string
    {
        $p = base_path('OPERATIONS.md');
        $this->assertFileExists($p);
        return (string) file_get_contents($p);
    }

    public function test_doc_exists(): void
    {
        $this->assertFileExists(base_path('OPERATIONS.md'));
    }

    public function test_documents_env_keys(): void
    {
        $src = $this->doc();
        foreach (['APP_ENV', 'APP_DEBUG', 'MPESA_KILL_SWITCH', 'MPESA_CALLBACK_SECRET', 'TRUSTED_PROXIES', 'CURRENT_TERM'] as $k) {
            $this->assertStringContainsString($k, $src, "OPERATIONS.md missing {$k}");
        }
    }

    public function test_documents_worker_start_commands(): void
    {
        $src = $this->doc();
        $this->assertStringContainsString('deploy/termux-worker.sh', $src);
        $this->assertStringContainsString('supervisorctl', $src);
    }

    public function test_documents_backup_and_restore(): void
    {
        $src = $this->doc();
        $this->assertStringContainsString('mysqldump', $src);
        $this->assertStringContainsString('backups', $src);
    }

    public function test_documents_pre_real_payment_checklist(): void
    {
        $src = $this->doc();
        $this->assertStringContainsString('Before running a real payment', $src);
    }

    public function test_documents_kill_switch(): void
    {
        $this->assertStringContainsString('MPESA_KILL_SWITCH', $this->doc());
    }
}
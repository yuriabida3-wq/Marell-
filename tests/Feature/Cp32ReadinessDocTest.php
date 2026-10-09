<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-32: payments readiness assessment exists and covers the required
 * topics. Does not assert the verdict — the verdict is for the owner.
 */
class Cp32ReadinessDocTest extends TestCase
{
    private function doc(): string
    {
        $p = base_path('PAYMENTS_READINESS.md');
        $this->assertFileExists($p);
        return (string) file_get_contents($p);
    }

    public function test_doc_exists(): void
    {
        $this->assertFileExists(base_path('PAYMENTS_READINESS.md'));
    }

    public function test_has_summary_verdict(): void
    {
        $this->assertStringContainsString('Summary verdict', $this->doc());
    }

    public function test_has_gate_table(): void
    {
        $this->assertStringContainsString('Go / No-Go gate table', $this->doc());
    }

    public function test_names_owner_actions(): void
    {
        $src = $this->doc();
        foreach (['Non-privileged DB user', 'Restore drill', 'Safaricom Daraja', 'Finance MFA', 'Accountant review'] as $item) {
            $this->assertStringContainsString($item, $src, "Missing owner action: {$item}");
        }
    }

    public function test_names_open_gates(): void
    {
        $src = $this->doc();
        foreach (['Stale-pending sweeper', 'Non-privileged DB user', 'Finance MFA'] as $gate) {
            $this->assertStringContainsString($gate, $src, "Missing gate: {$gate}");
        }
    }

    public function test_cites_commit_hashes(): void
    {
        $src = $this->doc();
        // MET gates must cite evidence.
        $this->assertStringContainsString('2becf95', $src); // CP-01
        $this->assertStringContainsString('ca851d9', $src); // CP-27
        $this->assertStringContainsString('add83eb', $src); // CP-15
        $this->assertStringContainsString('90de9f4', $src); // CP-24
    }

    public function test_declares_scope_limits(): void
    {
        $src = $this->doc();
        $this->assertStringContainsString('What this document is not', $src);
    }
}
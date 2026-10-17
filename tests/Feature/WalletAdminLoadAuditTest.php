<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Audit wiring: wallet admin load must produce an audit_logs row.
 * Login/role-change auditing already exists via LoginActivity and user.updated.
 */
class WalletAdminLoadAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'principal']);
        Role::create(['name' => 'bursar']);
    }

    private function principal(): User
    {
        $user = User::factory()->create(['role_label' => 'principal']);
        $user->assignRole('principal');
        return $user;
    }

    public function test_admin_load_writes_audit_log(): void
    {
        $principal = $this->principal();
        $student   = Student::factory()->create(['wallet_balance' => 0]);

        $response = $this->actingAs($principal)
            ->post('/principal/wallet/load', [
                'student_id' => $student->id,
                'amount'     => 500,
                'notes'      => 'Test top-up',
            ]);

        $response->assertRedirect();
        $this->assertSame(500.0, (float) $student->fresh()->wallet_balance);

        $this->assertDatabaseHas('audit_logs', [
            'action'     => 'wallet.admin_load',
            'model_type' => Student::class,
            'model_id'   => $student->id,
        ]);

        $log = AuditLog::where('action', 'wallet.admin_load')->first();
        $this->assertNotNull($log);
        $this->assertSame(500.0, (float) $log->after['amount']);
        $this->assertSame(500.0, (float) $log->after['new_balance']);
        $this->assertSame($principal->id, $log->user_id);
    }

    public function test_admin_load_updates_wallet_balance(): void
    {
        $principal = $this->principal();
        $student   = Student::factory()->create(['wallet_balance' => 100]);

        $this->actingAs($principal)
            ->post('/principal/wallet/load', [
                'student_id' => $student->id,
                'amount'     => 250,
                'notes'      => 'Test',
            ]);

        $this->assertSame('350.00', (string) $student->fresh()->wallet_balance);
    }
}

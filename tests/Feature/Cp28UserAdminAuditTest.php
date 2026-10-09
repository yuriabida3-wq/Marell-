<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-28: audit logging + role allowlist on UserAdmin.
 */
class Cp28UserAdminAuditTest extends TestCase
{
    private function src(): string
    {
        return (string) file_get_contents(base_path('app/Http/Controllers/UserAdminController.php'));
    }

    public function test_allowlist_constant_exists_and_excludes_parent(): void
    {
        $src = $this->src();
        $this->assertStringContainsString(
            "private const ALLOWED_ROLES = ['principal', 'dos', 'bursar', 'teacher', 'security']",
            $src
        );
        // Ensure the allowlist array literal itself does not include 'parent'.
        // (The const's docblock mentions 'parent' as excluded; that is fine.)
        $this->assertDoesNotMatchRegularExpression(
            "/ALLOWED_ROLES\s*=\s*\[[^\]]*'parent'/",
            $src,
            'parent must not appear inside the ALLOWED_ROLES array literal'
        );
    }

    public function test_store_uses_allowlist_not_raw_exists(): void
    {
        $src = $this->src();
        $this->assertStringContainsString(
            "'role'     => 'required|string|in:' . implode(',', self::ALLOWED_ROLES),",
            $src
        );
        $this->assertStringNotContainsString(
            "'role'     => 'required|string|exists:roles,name',",
            $src
        );
    }

    public function test_store_logs_user_created(): void
    {
        $src = $this->src();
        $this->assertStringContainsString("AuditLog::log('user.created'", $src);
    }

    public function test_update_logs_user_updated_with_role_change(): void
    {
        $src = $this->src();
        $this->assertStringContainsString("AuditLog::log('user.updated'", $src);
        $this->assertStringContainsString("'role_old'  => \$oldRole", $src);
        $this->assertStringContainsString("'role_new'  => \$data['role']", $src);
    }

    public function test_update_captures_old_role_before_sync(): void
    {
        $src = $this->src();
        $oldPos  = strpos($src, "\$oldRole = \$user->getRoleNames()->first();");
        $syncPos = strpos($src, "\$user->syncRoles([\$data['role']]);");
        $this->assertNotFalse($oldPos);
        $this->assertNotFalse($syncPos);
        $this->assertLessThan($syncPos, $oldPos, 'old role must be captured before syncRoles');
    }

    public function test_no_password_pin_or_token_in_audit_payloads(): void
    {
        $src = $this->src();
        // Extract blocks after each AuditLog::log call and confirm no secrets.
        preg_match_all('/AuditLog::log\((.*?)\);/s', $src, $m);
        foreach ($m[1] as $block) {
            $this->assertStringNotContainsString("'password'", $block);
            $this->assertStringNotContainsString("'pin'", $block);
            $this->assertStringNotContainsString("'token'", $block);
        }
    }

    public function test_existing_audit_calls_still_present(): void
    {
        $src = $this->src();
        $this->assertStringContainsString("AuditLog::log('user.password_reset'", $src);
        $this->assertStringContainsString("AuditLog::log('user.status_changed'", $src);
    }
}
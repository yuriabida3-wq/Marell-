<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-07: Africa/Nairobi timezone + explicit DB session timezone.
 */
class Cp07TimezoneTest extends TestCase
{
    public function test_app_timezone_is_nairobi(): void
    {
        $this->assertSame('Africa/Nairobi', config('app.timezone'));
    }

    public function test_mysql_connection_has_explicit_timezone(): void
    {
        $this->assertSame(
            '+03:00',
            config('database.connections.mysql.timezone'),
            'mysql connection must carry an explicit +03:00 timezone'
        );
    }

    public function test_mariadb_connection_has_explicit_timezone(): void
    {
        $this->assertSame('+03:00', config('database.connections.mariadb.timezone'));
    }

    public function test_bootstrap_comment_no_longer_misleads(): void
    {
        $src = (string) file_get_contents(base_path('bootstrap/app.php'));
        $this->assertStringNotContainsString(
            'Every minute during school days',
            $src,
            'stale comment above fees:autopilot still present'
        );
    }

    public function test_now_reports_nairobi(): void
    {
        // now() must use the app timezone.
        $this->assertSame('Africa/Nairobi', now()->getTimezone()->getName());
    }
}
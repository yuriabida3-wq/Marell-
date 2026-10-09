<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-25: stray backup and archive files removed from project.
 */
class Cp25StrayFilesTest extends TestCase
{
    public function test_no_bak_files_in_controllers(): void
    {
        $this->assertSame([], glob(base_path('app/Http/Controllers/*.bak')));
    }

    public function test_no_rN_files_in_controllers(): void
    {
        $this->assertSame([], glob(base_path('app/Http/Controllers/*.r[0-9]')));
    }

    public function test_no_bak_in_models(): void
    {
        $this->assertSame([], glob(base_path('app/Models/*.bak')));
    }

    public function test_no_working_in_jobs(): void
    {
        $this->assertSame([], glob(base_path('app/Jobs/*.working')));
    }

    public function test_no_bak_in_views(): void
    {
        $this->assertSame([], glob(base_path('resources/views/*/*.bak')));
    }

    public function test_routes_backup_gone(): void
    {
        $this->assertFileDoesNotExist(base_path('routes/web.php.bak'));
    }

    public function test_project_zip_gone(): void
    {
        $this->assertFileDoesNotExist(base_path('project.zip'));
    }

    public function test_vendor_zip_gone(): void
    {
        $this->assertFileDoesNotExist(base_path('vendor.zip'));
    }

    public function test_gitignore_still_covers_bak_and_zip(): void
    {
        $src = (string) file_get_contents(base_path('.gitignore'));
        $this->assertStringContainsString('*.bak', $src);
        $this->assertStringContainsString('*.zip', $src);
    }
}
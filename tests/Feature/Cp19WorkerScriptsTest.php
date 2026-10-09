<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-19 (revised): scheduler supervision for VPS and Termux.
 */
class Cp19WorkerScriptsTest extends TestCase
{
    public function test_scheduler_supervisor_conf_exists(): void
    {
        $path = base_path('deploy/jenga-scheduler.conf');
        $this->assertFileExists($path);
        $src = (string) file_get_contents($path);
        $this->assertStringContainsString('program:jenga-scheduler', $src);
        $this->assertStringContainsString('schedule:work', $src);
        $this->assertStringContainsString('autorestart=true', $src);
        $this->assertStringContainsString('numprocs=1', $src);
    }

    public function test_worker_supervisor_conf_unchanged(): void
    {
        $src = (string) file_get_contents(base_path('deploy/jenga-worker.conf'));
        $this->assertStringContainsString('program:jenga-worker', $src);
        $this->assertStringContainsString('queue:work', $src);
        $this->assertStringContainsString('mpesa,default', $src);
        $this->assertStringContainsString('numprocs=2', $src);
        $this->assertStringContainsString('autorestart=true', $src);
    }

    public function test_termux_script_starts_queue_and_scheduler(): void
    {
        $src = (string) file_get_contents(base_path('deploy/termux-worker.sh'));
        $this->assertStringContainsString('queue:work --queue=mpesa,default', $src);
        $this->assertStringContainsString('schedule:run', $src);
        $this->assertStringContainsString('--max-time=3600', $src);
    }

    public function test_termux_script_writes_pid_files(): void
    {
        $src = (string) file_get_contents(base_path('deploy/termux-worker.sh'));
        $this->assertStringContainsString('worker.pid', $src);
        $this->assertStringContainsString('scheduler.pid', $src);
    }

    public function test_termux_script_does_not_hardcode_home_path(): void
    {
        $src = (string) file_get_contents(base_path('deploy/termux-worker.sh'));
        $this->assertStringNotContainsString('~/projects/marell', $src);
        $this->assertStringContainsString('SCRIPT_DIR=', $src);
    }

    public function test_deploy_readme_exists_and_documents_both_hosts(): void
    {
        $path = base_path('deploy/README.md');
        $this->assertFileExists($path);
        $src = (string) file_get_contents($path);
        $this->assertStringContainsString('Supervisor', $src);
        $this->assertStringContainsString('Termux', $src);
    }
}
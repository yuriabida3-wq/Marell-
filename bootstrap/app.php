<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:      __DIR__.'/../routes/web.php',
        api:      __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health:   '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trust X-Forwarded-For only from explicitly listed proxy IPs.
        // Empty list = trust no proxy = use the honest TCP peer IP.
        $trustedProxies = trim((string) env('TRUSTED_PROXIES', ''));
        if ($trustedProxies !== '') {
            $middleware->trustProxies(at: array_filter(array_map('trim', explode(',', $trustedProxies))));
        }

        $middleware->validateCsrfTokens(except: [
            'api/mpesa/callback',
        ]);

        $middleware->append(
            \App\Http\Middleware\SecurityHeaders::class,
        );

        $middleware->appendToGroup('web', [
            \App\Http\Middleware\SessionTimeout::class,
        ]);

        $middleware->alias([
            'parent.auth'        => \App\Http\Middleware\ParentAuth::class,
            'guard.or.staff'     => \App\Http\Middleware\GuardOrStaff::class,
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        // Daily 7am — send defaulter summary to Director
        $schedule->call(function () {
            $phone = config('services.sms.director_phone') ?: '254700000001';
            \App\Jobs\SendDailyDefaulterReport::dispatch($phone)->onQueue('default');
        })->dailyAt('07:00')->name('daily-defaulter-report')->withoutOverlapping();

        // Daily 2am — DB backup reminder (logs only)
        $schedule->call(function () {
            \Log::info('Nightly backup checkpoint: ' . now());
        })->dailyAt('02:00')->name('nightly-checkpoint');

        // Daily 8am EAT — run fee autopilot (fee escalation stages)
        $schedule->command('fees:autopilot')->dailyAt('08:00')->name('fee-autopilot');

        $schedule->command('fines:apply')->dailyAt('06:55')->name('auto-late-fines');

        // L4: resolve payments stuck in pending. Config-gated so the schedule
        // only fires when the owner enables it via MPESA_STUCK_RESOLVER_ENABLED.
        if (config('services.mpesa.stuck_resolver_enabled', false)) {
            $schedule->job(new \App\Jobs\ResolveStuckPayments)
                ->everyTwoMinutes()
                ->name('resolve-stuck-payments')
                ->withoutOverlapping();
        }
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

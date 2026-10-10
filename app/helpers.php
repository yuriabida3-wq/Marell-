<?php

if (!function_exists('role_layout')) {
    function role_layout(): string
    {
        $role = optional(auth()->user())->getRoleNames()->first() ?? 'dos';
        return match ($role) {
            'principal' => 'layouts.admin',
            'bursar'    => 'layouts.bursar',
            'teacher'   => 'layouts.teacher',
            'parent'    => 'layouts.public',
            default     => 'layouts.dos',
        };
    }
}

if (!function_exists('setting')) {
    /**
     * Read a Skuli360 setting with three-tier fallback.
     *
     *   1. settings table   (per-school, admin-editable)
     *   2. $default         (explicit fallback from the caller)
     *   3. config('skuli.defaults.NAME')  (safe defaults declared in config)
     *
     * Returns null only if all three tiers are null or absent. Returns the
     * empty string when a setting was explicitly cleared by an admin.
     */
    function setting(string $name, mixed $default = null): mixed
    {
        $value = null;

        try {
            $value = \App\Models\Setting::get($name);
        } catch (\Throwable $e) {
            // Settings table missing (pre-migration) or DB unreachable.
            // Fall through to config defaults. Never break a page render.
            $value = null;
        }

        if ($value !== null) {
            return $value;
        }

        if ($default !== null) {
            return $default;
        }

        return config('skuli.defaults.' . $name);
    }
}

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

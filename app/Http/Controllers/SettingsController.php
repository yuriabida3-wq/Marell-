<?php

namespace App\Http\Controllers;

use App\Models\Setting;

class SettingsController extends Controller
{
    public function index()
    {
        $defaults = config('skuli.defaults', []);

        $groups = [
            'School identity' => [
                'school_name', 'school_short_name', 'school_motto',
                'school_phone', 'school_email', 'school_address', 'school_website',
            ],
            'Branding' => [
                'school_logo', 'school_favicon', 'brand_primary', 'brand_accent',
            ],
            'Communication' => [
                'sms_sender_id', 'currency_symbol',
            ],
            'Social links' => [
                'social_facebook', 'social_instagram', 'social_x',
                'social_youtube', 'social_whatsapp',
            ],
        ];

        $rows = [];
        foreach ($groups as $groupName => $keys) {
            $rows[$groupName] = [];
            foreach ($keys as $key) {
                $dbValue   = Setting::get($key);       // null when never written
                $effective = setting($key);            // value with three-tier fallback
                $default   = $defaults[$key] ?? null;

                $rows[$groupName][] = [
                    'key'       => $key,
                    'db_value'  => $dbValue,
                    'effective' => $effective,
                    'default'   => $default,
                    'source'    => $dbValue !== null ? 'database' : 'config default',
                ];
            }
        }

        return view('principal.settings', compact('rows'));
    }
}

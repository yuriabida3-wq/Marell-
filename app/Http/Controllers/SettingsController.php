<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    private const GROUPS = [
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

    public function index()
    {
        $defaults = config('skuli.defaults', []);
        $rows = [];

        foreach (self::GROUPS as $groupName => $keys) {
            $rows[$groupName] = [];
            foreach ($keys as $key) {
                $dbValue = Setting::get($key);
                $rows[$groupName][] = [
                    'key'       => $key,
                    'db_value'  => $dbValue,
                    'effective' => setting($key),
                    'default'   => $defaults[$key] ?? null,
                    'source'    => $dbValue !== null ? 'database' : 'config default',
                ];
            }
        }

        return view('principal.settings', compact('rows'));
    }

    public function update(Request $request)
    {
        $defaults = config('skuli.defaults', []);
        $allowed  = array_merge(...array_values(self::GROUPS));

        // Read only whitelisted keys that were actually posted
        $payload = $request->all();
        $data = [];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $payload)) {
                $data[$key] = $payload[$key];
            }
        }

        if (empty($data)) {
            return back()->withErrors(['settings' => 'No recognised settings in submission.']);
        }

        // Validate BEFORE any writes
        $rules = [
            'school_email'     => 'nullable|email|max:255',
            'school_website'   => 'nullable|url|max:255',
            'school_phone'     => 'nullable|string|max:50',
            'brand_primary'    => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'brand_accent'     => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'social_facebook'  => 'nullable|url|max:255',
            'social_instagram' => 'nullable|url|max:255',
            'social_x'         => 'nullable|url|max:255',
            'social_youtube'   => 'nullable|url|max:255',
            'social_whatsapp'  => 'nullable|url|max:255',
        ];
        validator($data, array_intersect_key($rules, $data))->validate();

        // Write: empty or equal-to-default reverts to default (row deleted).
        // Anything else persists.
        foreach ($data as $key => $value) {
            $value   = is_string($value) ? trim($value) : $value;
            $default = $defaults[$key] ?? null;

            if ($value === null || $value === '' || $value === $default) {
                Setting::forget($key);
            } else {
                Setting::set($key, $value);
            }
        }

        Setting::flushCache();

        return redirect()->route('principal.settings')->with('success', 'Settings saved.');
    }
}

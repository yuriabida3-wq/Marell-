<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Product Identity
    |--------------------------------------------------------------------------
    |
    | Skuli360 / Jenga Web Technologies values. Same for every school install.
    | Never overridden by a school setting. If Jenga Web rebrands, change only
    | here.
    |
    */

    'product' => [
        'name'        => 'Skuli360',
        'vendor'      => 'Jenga Web Technologies',
        'vendor_url'  => '',
        'footer_text' => 'Powered by Skuli360 · Jenga Web Technologies',
    ],

    /*
    |--------------------------------------------------------------------------
    | Setting Defaults
    |--------------------------------------------------------------------------
    |
    | Safe fallbacks for settings a school has not yet configured. The
    | setting() helper resolves in this order:
    |
    |   1. settings table (per-school, admin-editable)
    |   2. explicit $default argument passed to the helper
    |   3. config('skuli.defaults.NAME')  -- these values
    |
    | Adding a key here does NOT create a settings row. It only declares what
    | the value should be when a school has never set it.
    |
    */

    'defaults' => [

        // School identity
        'school_name'       => 'Your School',
        'school_short_name' => 'School',
        'school_motto'      => 'Excellence in Education',
        'school_phone'      => '',
        'school_email'      => '',
        'school_address'    => 'Kenya',
        'school_website'    => '',

        // Branding
        'school_logo'       => null,   // stored path under public/
        'school_favicon'    => null,   // stored path under public/
        'brand_primary'     => '#1D4ED8',
        'brand_accent'      => '#D4AF37',

        // Communication
        'sms_sender_id'     => 'SKULI',
        'currency_symbol'   => 'KSh',

        // Social links (optional -- empty means do not render the link)
        'social_facebook'   => '',
        'social_instagram'  => '',
        'social_x'          => '',
        'social_youtube'    => '',
        'social_whatsapp'   => '',

    ],

    /*
    |--------------------------------------------------------------------------
    | Settings Cache
    |--------------------------------------------------------------------------
    |
    | Every read of setting() is cached. Writes clear the specific key. To
    | invalidate ALL cached settings at once (e.g. after a code change that
    | alters how a value is interpreted), bump 'version'.
    |
    */

    'cache' => [
        'version' => 'v1',
        'ttl'     => 86400, // seconds -- 24h. Writes clear immediately.
    ],

];

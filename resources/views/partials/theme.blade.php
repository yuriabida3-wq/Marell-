{{--
 | SKULI360 THEME PARTIAL
 |
 | @include('partials.theme')  -- inside <head>, after <meta charset> and <title>
 |
 | Emits: csrf meta, theme-color, favicon, PWA manifest, Tailwind CDN with
 | CSS-variable colour config, and the full CSS variable stack for light and
 | dark. Does NOT load Google Fonts (spec §2.3). Uses system font stack.
 |
 | To force dark on a screen (guard console, canteen, security):
 |   <html class="theme-dark">
 | Nothing else is required -- the variables below take over.
 |
 | To re-theme every page at once, change `brand_primary` or `brand_accent`
 | in the settings table. All shades are derived server-side.
--}}

@php
    $primary = setting('brand_primary', '#1D4ED8');
    $accent  = setting('brand_accent', '#D4AF37');

    $hexToRgb = function (string $hex): array {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
            return [29, 78, 216]; // fallback to spec default #1D4ED8
        }
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    };

    $tint = function (array $rgb, float $ratio): array {
        // ratio 0 = unchanged, 1 = white
        return array_map(fn ($c) => (int) round($c + (255 - $c) * $ratio), $rgb);
    };

    $shade = function (array $rgb, float $ratio): array {
        // ratio 0 = black, 1 = unchanged
        return array_map(fn ($c) => (int) round($c * $ratio), $rgb);
    };

    $rgbStr = fn (array $rgb): string => implode(' ', $rgb);

    $pr = $hexToRgb($primary);
    $ac = $hexToRgb($accent);

    $shades = [
        '50'  => $rgbStr($tint($pr, 0.95)),
        '100' => $rgbStr($tint($pr, 0.88)),
        '200' => $rgbStr($tint($pr, 0.72)),
        '300' => $rgbStr($tint($pr, 0.50)),
        '400' => $rgbStr($tint($pr, 0.25)),
        '500' => $rgbStr($pr),
        '600' => $rgbStr($shade($pr, 0.90)),
        '700' => $rgbStr($shade($pr, 0.78)),
        '800' => $rgbStr($shade($pr, 0.62)),
        '900' => $rgbStr($shade($pr, 0.48)),
    ];

    $favicon = setting('school_favicon');
@endphp

<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="{{ $primary }}">

@if ($favicon)
    <link rel="icon" href="{{ asset($favicon) }}">
@else
    <link rel="icon" href="/icon-192.png">
@endif

<link rel="manifest" href="/manifest.webmanifest">

<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: {
    extend: {
      colors: {
        primary: {
          50:  'rgb(var(--c-primary-50) / <alpha-value>)',
          100: 'rgb(var(--c-primary-100) / <alpha-value>)',
          200: 'rgb(var(--c-primary-200) / <alpha-value>)',
          300: 'rgb(var(--c-primary-300) / <alpha-value>)',
          400: 'rgb(var(--c-primary-400) / <alpha-value>)',
          500: 'rgb(var(--c-primary-500) / <alpha-value>)',
          600: 'rgb(var(--c-primary-600) / <alpha-value>)',
          700: 'rgb(var(--c-primary-700) / <alpha-value>)',
          800: 'rgb(var(--c-primary-800) / <alpha-value>)',
          900: 'rgb(var(--c-primary-900) / <alpha-value>)',
          DEFAULT: 'rgb(var(--c-primary-500) / <alpha-value>)',
        },
        accent:    'rgb(var(--c-accent) / <alpha-value>)',
        surface:   'rgb(var(--c-surface) / <alpha-value>)',
        'surface-2': 'rgb(var(--c-surface-2) / <alpha-value>)',
        bordersoft: 'rgb(var(--c-border) / <alpha-value>)',
        bodytext:  'rgb(var(--c-text) / <alpha-value>)',
        mutedtext: 'rgb(var(--c-muted) / <alpha-value>)',
        success:   'rgb(var(--c-success) / <alpha-value>)',
        warning:   'rgb(var(--c-warning) / <alpha-value>)',
        danger:    'rgb(var(--c-danger) / <alpha-value>)',
        info:      'rgb(var(--c-info) / <alpha-value>)',
      },
      fontFamily: {
        poppins: ['Poppins', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        inter:   ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        sans:    ['ui-sans-serif', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'sans-serif'],
      },
    },
  },
};
</script>

<style>
:root {
  --c-primary-50:  {{ $shades['50'] }};
  --c-primary-100: {{ $shades['100'] }};
  --c-primary-200: {{ $shades['200'] }};
  --c-primary-300: {{ $shades['300'] }};
  --c-primary-400: {{ $shades['400'] }};
  --c-primary-500: {{ $shades['500'] }};
  --c-primary-600: {{ $shades['600'] }};
  --c-primary-700: {{ $shades['700'] }};
  --c-primary-800: {{ $shades['800'] }};
  --c-primary-900: {{ $shades['900'] }};
  --c-primary:     {{ $shades['500'] }};
  --c-accent:      {{ implode(' ', $ac) }};

  --c-bg:        244 247 251;
  --c-surface:   255 255 255;
  --c-surface-2: 248 250 252;
  --c-border:    226 232 240;
  --c-text:      15 23 42;
  --c-muted:     100 116 139;
  --c-success:   22 163 74;
  --c-warning:   245 158 11;
  --c-danger:    220 38 38;
  --c-info:      14 165 233;

  --sidebar-from: 30 58 138;
  --sidebar-to:   37 99 235;
}

.theme-dark {
  --c-bg:        11 18 32;
  --c-surface:   17 26 46;
  --c-surface-2: 22 33 58;
  --c-border:    30 42 68;
  --c-text:      229 237 255;
  --c-muted:     148 163 184;
  --sidebar-from: 8 15 35;
  --sidebar-to:   17 26 60;
}

html {
  background-color: rgb(var(--c-bg));
  color: rgb(var(--c-text));
  font-family: ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
  -webkit-text-size-adjust: 100%;
}

body { font-family: inherit; }

h1, h2, h3, h4 {
  font-family: ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
  font-weight: 700;
  letter-spacing: -0.01em;
}
</style>

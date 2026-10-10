{{--
 | <x-brand-logo size="sm|md|lg" />
 |
 | Two automatic modes:
 |   1. school_logo setting is set  -> renders the school's logo + school name
 |   2. school_logo is empty        -> renders the Skuli360 mark + wordmark
 |
 | The Skuli360 SVG uses currentColor, so it inherits the surrounding text
 | colour. It works on light headers, dark sidebars, and login cards without
 | any variant prop.
 |
 | Sizes:
 |   sm  32px mark, ~14px wordmark  (collapsed sidebar, compact bars)
 |   md  40px mark, ~18px wordmark  (default -- headers, sidebars)
 |   lg  56px mark, ~28px wordmark  (login pages, marketing hero)
--}}

@php
    $size     = $size ?? 'md';
    $logo     = setting('school_logo');
    $name     = (string) setting('school_name', 'Your School');
    $motto    = (string) setting('school_motto', '');

    [$markPx, $wordPx, $tagPx] = match ($size) {
        'sm'    => [32, 14, 9],
        'lg'    => [56, 28, 11],
        default => [40, 18, 10],
    };

    $nameParts = preg_split('/\s+/', trim($name), 2);
    $line1 = $nameParts[0] ?? $name;
    $line2 = $nameParts[1] ?? '';
@endphp

<div class="inline-flex items-center gap-2 leading-tight">

    @if ($logo)
        <img
            src="{{ asset($logo) }}"
            alt="{{ $name }}"
            style="width: {{ $markPx }}px; height: {{ $markPx }}px;"
            class="rounded-full object-cover shrink-0"
        >
    @else
        <svg viewBox="0 0 64 64"
             style="width: {{ $markPx }}px; height: {{ $markPx }}px;"
             class="shrink-0"
             aria-hidden="true"
             fill="none">
            <circle cx="32" cy="32" r="28"
                    stroke="currentColor" stroke-width="4"
                    stroke-dasharray="150 26" stroke-linecap="round"/>
            <path d="M32 18 12 28l20 10 20-10z" fill="currentColor"/>
            <path d="M20 34v8c0 4 6 8 12 8s12-4 12-8v-8l-12 6z" fill="currentColor"/>
        </svg>
    @endif

    @if ($logo)
        <div class="min-w-0">
            <div class="font-bold truncate" style="font-size: {{ $wordPx }}px;">
                {{ $line1 }}
            </div>
            @if ($line2 !== '')
                <div class="truncate uppercase tracking-widest opacity-80"
                     style="font-size: {{ $tagPx }}px;">
                    {{ $line2 }}
                </div>
            @elseif ($motto !== '')
                <div class="truncate uppercase tracking-widest opacity-80"
                     style="font-size: {{ $tagPx }}px;">
                    {{ $motto }}
                </div>
            @endif
        </div>
    @else
        <div class="font-extrabold" style="font-size: {{ $wordPx }}px;">
            <span>Skuli</span><span style="color: rgb(var(--c-accent));">360</span>
        </div>
    @endif

</div>

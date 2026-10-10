{{--
 | <x-button variant="..." size="..." icon="..." href="..." type="...">
 |
 | Variants:  gold (public primary CTA) | navy (staff primary) | outline |
 |            ghost | danger
 | Sizes:     sm (40px) | md (44px, default) | lg (48px)
 |
 | Behaviour:
 |   - With href          -> renders <a>
 |   - Without href       -> renders <button> (default type="submit")
 |   - loading="true"     -> prepends spinner, disables interaction
 |   - icon="..."         -> prepends <x-icon>
 |
 | Minimum touch target on md is 44px (WCAG), on lg is 48px (hero CTAs).
 | Focus ring uses the brand's primary colour so it follows the theme.
--}}

@props([
    'variant' => 'navy',
    'size'    => 'md',
    'icon'    => null,
    'href'    => null,
    'type'    => 'submit',
    'loading' => false,
])

@php
    $sizeClasses = match ($size) {
        'sm'    => 'text-xs px-3 py-2 min-h-[40px] gap-1.5',
        'lg'    => 'text-base px-6 py-3 min-h-[48px] gap-2.5',
        default => 'text-sm px-4 py-2.5 min-h-[44px] gap-2',
    };

    $variantClasses = match ($variant) {
        'gold'    => 'bg-accent text-navy hover:brightness-110 active:brightness-95 shadow-sm',
        'navy'    => 'bg-navy text-white hover:opacity-90 active:opacity-80 shadow-sm',
        'outline' => 'border-2 border-current text-navy bg-transparent hover:bg-navy hover:text-white',
        'outline-light' => 'border-2 border-white text-white bg-transparent hover:bg-white hover:text-navy',
        'ghost'   => 'text-navy bg-transparent hover:bg-gray-100',
        'subtle'  => 'bg-gray-200 text-navy hover:bg-gray-300 active:bg-gray-400',
        'danger'  => 'bg-red-600 text-white hover:bg-red-700 active:bg-red-800',
        default   => 'bg-navy text-white hover:opacity-90',
    };

    $base = 'inline-flex items-center justify-center font-semibold rounded-xl '
          . 'transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 '
          . 'focus-visible:ring-navy/60 disabled:opacity-60 disabled:pointer-events-none';

    $classes = trim($base . ' ' . $sizeClasses . ' ' . $variantClasses);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($loading)
            <svg class="animate-spin" style="width:16px;height:16px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" opacity=".25"/><path d="M21 12a9 9 0 0 1-9 9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
        @elseif ($icon)
            <x-icon :name="$icon" :size="$size === 'lg' ? 'md' : 'sm'" />
        @endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button type="{{ $type }}" @if($loading) disabled @endif {{ $attributes->merge(['class' => $classes]) }}>
        @if ($loading)
            <svg class="animate-spin" style="width:16px;height:16px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" opacity=".25"/><path d="M21 12a9 9 0 0 1-9 9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
        @elseif ($icon)
            <x-icon :name="$icon" :size="$size === 'lg' ? 'md' : 'sm'" />
        @endif
        <span>{{ $slot }}</span>
    </button>
@endif

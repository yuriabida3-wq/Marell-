{{--
 | <x-card title="..." subtitle="..." action="..." :action-href="..."
 |         padding="md|sm|none" variant="default|dark|gold" :hover="false">
 |
 | Slots:
 |   default         body content
 |   actions         optional custom right-side header content (overrides action label)
 |   footer          optional footer content
 |
 | Variants:
 |   default   white background, subtle border + shadow
 |   dark      navy background, white text
 |   gold      gold background, navy text
 |
 | Padding: md (default, 20-24px responsive) | sm (12-16px) | none (flush -- for tables/images)
--}}

@props([
    'title'       => null,
    'subtitle'    => null,
    'action'      => null,
    'actionHref'  => null,
    'padding'     => 'md',
    'variant'     => 'default',
    'hover'       => false,
    'href'        => null,
])

@php
    $variantClasses = match ($variant) {
        'dark'  => 'bg-navy text-white border-navy',
        'gold'  => 'bg-accent text-navy border-accent',
        default => 'bg-white text-bodytext border-gray-200',
    };

    $paddingClasses = match ($padding) {
        'none' => '',
        'sm'   => 'p-3 md:p-4',
        default => 'p-4 md:p-6',
    };

    $hoverClasses = $hover
        ? 'transition-shadow hover:shadow-lg'
        : '';

    $classes = trim(
        'rounded-2xl border shadow-sm overflow-hidden ' .
        $variantClasses . ' ' . $hoverClasses
    );
@endphp

@if ($href)
<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes . ' block']) }}>
@else
<div {{ $attributes->merge(['class' => $classes]) }}>
@endif

    @if ($title || $action || isset($actions))
        <div class="{{ $padding === 'none' ? 'p-4 md:p-6 pb-0' : 'pb-3 md:pb-4' }} flex items-start gap-3">
            <div class="flex-1 min-w-0">
                @if ($title)
                    <h3 class="font-bold text-base md:text-lg leading-tight">
                        {{ $title }}
                    </h3>
                @endif
                @if ($subtitle)
                    <div class="text-xs text-mutedtext mt-1">{{ $subtitle }}</div>
                @endif
            </div>

            <div class="shrink-0">
                @isset($actions)
                    {{ $actions }}
                @elseif ($action && $actionHref)
                    <a href="{{ $actionHref }}"
                       class="text-xs font-semibold text-navy hover:text-accent inline-flex items-center gap-1">
                        {{ $action }}
                        <x-icon name="arrow-right" size="sm" />
                    </a>
                @endif
            </div>
        </div>
    @endif

    <div class="{{ $paddingClasses }}">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="px-4 md:px-6 py-3 border-t border-current/10 bg-black/[0.02]">
            {{ $footer }}
        </div>
    @endisset

@if ($href)
</a>
@else
</div>
@endif

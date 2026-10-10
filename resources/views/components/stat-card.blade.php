{{--
 | <x-stat-card label="..." value="..." icon="..." tint="primary" />
 |
 | KPI tile for dashboards. Renders label, big value, optional caption,
 | optional icon tile, optional trend indicator, optional link wrapper.
 |
 | Tints: primary | accent | success | warning | danger | info
 | Compose over <x-card>. Uses theme semantic tokens only (bg-surface,
 | text-bodytext, text-mutedtext) so .theme-dark flips it automatically.
 |
 | data-count: emitted only when `count` prop is passed (numeric). The
 | count-up animation is the caller's responsibility; this component
 | just renders the marker.
--}}
@props([
    'label',
    'value',
    'count' => null,
    'icon' => null,
    'tint' => 'primary',
    'caption' => null,
    'trend' => null,
    'trendValue' => null,
    'href' => null,
])

@php
$tints = [
    'primary' => 'bg-primary-50 text-primary-700',
    'accent'  => 'bg-accent/10 text-accent',
    'success' => 'bg-success/10 text-success',
    'warning' => 'bg-warning/10 text-warning',
    'danger'  => 'bg-danger/10 text-danger',
    'info'    => 'bg-info/10 text-info',
];
$tintClasses = $tints[$tint] ?? $tints['primary'];
@endphp

<x-card :href="$href" padding="md" :hover="(bool) $href">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <div class="text-[10px] tracking-widest font-bold text-mutedtext uppercase">{{ $label }}</div>
            <div class="text-2xl md:text-3xl font-extrabold text-bodytext mt-2 tabular-nums"
                 @if($count !== null) data-count="{{ $count }}" @endif>{{ $value }}</div>
            @if($caption)
                <div class="text-xs text-mutedtext mt-1">{{ $caption }}</div>
            @endif
            @if($trend && $trendValue)
                <div class="text-xs mt-1 font-semibold {{ $trend === 'up' ? 'text-success' : 'text-danger' }}">
                    {{ $trend === 'up' ? '↑' : '↓' }} {{ $trendValue }}
                </div>
            @endif
        </div>
        @if($icon)
            <div class="shrink-0 w-11 h-11 rounded-xl grid place-items-center {{ $tintClasses }}">
                <x-icon :name="$icon" size="md" />
            </div>
        @endif
    </div>
</x-card>

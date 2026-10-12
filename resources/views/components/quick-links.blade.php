{{--
 | <x-quick-links :items="$tiles" />
 |
 | Grid of tappable pastel icon tiles. Each item is an array with:
 |   label  (string, required)  visible text below the icon
 |   href   (string, required)  destination (route() or raw path)
 |   icon   (string, optional)  x-icon name; tile renders text-only if absent
 |   tint   (string, optional)  primary | accent | success | warning | danger | info
 |
 | Default 2 columns on phone, 4 on md+. Override via columns="2|3|4".
 | Uses theme semantic tokens (bg-surface, bordersoft, text-bodytext) so
 | .theme-dark flips it automatically.
--}}
@props([
    'items' => [],
    'columns' => 4,
])

@php
$colClass = match ((int) $columns) {
    2 => 'grid-cols-2',
    3 => 'grid-cols-2 md:grid-cols-3',
    default => 'grid-cols-2 md:grid-cols-4',
};
$tints = [
    'primary' => 'bg-primary-50 text-primary-700',
    'accent'  => 'bg-accent/10 text-accent',
    'success' => 'bg-success/10 text-success',
    'warning' => 'bg-warning/10 text-warning',
    'danger'  => 'bg-danger/10 text-danger',
    'info'    => 'bg-info/10 text-info',
];
@endphp

@if (!empty($items))
<div {{ $attributes->merge(['class' => 'grid ' . $colClass . ' gap-4']) }}>
    @foreach ($items as $item)
        @php
            $tintClass = $tints[$item['tint'] ?? 'primary'] ?? $tints['primary'];
        @endphp
        <a href="{{ $item['href'] }}" class="bg-surface rounded-2xl p-5 border bordersoft shadow-sm hover:shadow-lg transition text-center block">
            @if (!empty($item['icon']))
                <div class="w-12 h-12 rounded-xl grid place-items-center mx-auto mb-3 {{ $tintClass }}">
                    <x-icon :name="$item['icon']" size="lg" />
                </div>
            @endif
            <div class="font-bold text-bodytext text-sm">{{ $item['label'] }}</div>
        </a>
    @endforeach
</div>
@endif

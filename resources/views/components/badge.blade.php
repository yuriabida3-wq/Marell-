{{--
 | <x-badge variant="success">Completed</x-badge>
 |
 | Pill badge. Text or icon in the default slot.
 |
 | Variants: success | warning | danger | info | neutral
 | Uses theme semantic tokens so .theme-dark flips colours automatically.
 | Minimum height 20px; inline-flex; rounded-full.
--}}
@props([
    'variant' => 'neutral',
])

@php
$variants = [
    'success' => 'bg-success/10 text-success',
    'warning' => 'bg-warning/10 text-warning',
    'danger'  => 'bg-danger/10 text-danger',
    'info'    => 'bg-info/10 text-info',
    'neutral' => 'bg-surface-2 text-mutedtext',
];
$classes = $variants[$variant] ?? $variants['neutral'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2 py-1 rounded-full text-xs font-bold ' . $classes]) }}>{{ $slot }}</span>

{{--
 | <x-input name="..." label="..." type="text" :value="..." hint="..." required />
 |
 | A single form input with an optional label and hint.
 |
 | IMPORTANT: `name` is required and is never auto-generated. The component
 | throws if it is missing. This is a money-path safety guard -- no form field
 | name can be renamed by accident because the fix would refuse to render,
 | not fail silently.
 |
 | Hidden inputs are NOT the target of this component. Keep those as raw
 | <input type="hidden"> so multiple inputs with the same name on one page
 | do not produce duplicate IDs.
--}}

@props([
    'name'        => null,
    'label'       => null,
    'type'        => 'text',
    'value'       => null,
    'placeholder' => null,
    'required'    => false,
    'hint'        => null,
    'min'         => null,
    'max'         => null,
    'step'        => null,
])

@php
    if (! $name) {
        throw new \RuntimeException(
            '<x-input> requires a name attribute. Field names are frozen -- never auto-generate them.'
        );
    }

    $baseClasses = 'w-full min-h-[48px] rounded-xl border-2 border-gray-200 px-4 focus:border-gold focus:outline-none';
@endphp

<div>
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-semibold text-navy mb-2">
            {{ $label }}@if ($required) <span class="text-red-600">*</span>@endif
        </label>
    @endif

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($value !== null && $value !== '') value="{{ $value }}" @endif
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($required) required @endif
        @if ($min !== null) min="{{ $min }}" @endif
        @if ($max !== null) max="{{ $max }}" @endif
        @if ($step !== null) step="{{ $step }}" @endif
        {{ $attributes->merge(['class' => $baseClasses]) }}
    >

    @if ($hint)
        <div class="text-xs text-gray-500 mt-1">{{ $hint }}</div>
    @endif
</div>

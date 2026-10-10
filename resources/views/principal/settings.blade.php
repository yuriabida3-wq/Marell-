@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
<div class="p-4 md:p-6 max-w-4xl mx-auto">

  <div class="mb-6">
    <h1 class="text-2xl font-bold text-navy">Settings</h1>
    <p class="text-sm text-gray-600 mt-1">
      School-wide settings. Values stored here override the Skuli360 defaults.
      Leave a field blank to revert to default.
    </p>
  </div>

  @if (session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-900 text-sm px-4 py-3">
      {{ session('success') }}
    </div>
  @endif

  @if ($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-900 text-sm px-4 py-3">
      <ul class="list-disc list-inside">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @foreach ($rows as $groupName => $items)
    <form method="POST" action="{{ route('principal.settings.update') }}">
      @csrf
      <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
          <div class="font-semibold text-navy">{{ $groupName }}</div>
          <button type="submit"
                  class="px-3 py-1.5 rounded-lg bg-navy text-white text-xs font-semibold hover:opacity-90">
            Save {{ $groupName }}
          </button>
        </div>
        <table class="w-full text-sm">
          <tbody>
          @foreach ($items as $row)
            @php
              $dbValue    = $row['db_value'];
              $placeholder = $row['default'] ?? '';
              $isColor    = in_array($row['key'], ['brand_primary', 'brand_accent'], true);
              $inputType  = match (true) {
                $isColor                                 => 'color',
                $row['key'] === 'school_email'           => 'email',
                $row['key'] === 'school_website'         => 'url',
                $row['key'] === 'school_phone'           => 'tel',
                str_starts_with($row['key'], 'social_')  => 'url',
                default                                  => 'text',
              };
              $inputValue = $dbValue ?? '';
              if ($isColor && $inputValue === '') {
                  $inputValue = $placeholder ?: '#1D4ED8';
              }
            @endphp
            <tr class="border-b border-gray-100 last:border-b-0">
              <td class="px-4 py-3 align-top w-1/3">
                <div class="font-medium text-gray-900">
                  {{ ucwords(str_replace('_', ' ', $row['key'])) }}
                </div>
                <div class="text-xs text-gray-500 font-mono">{{ $row['key'] }}</div>
              </td>
              <td class="px-4 py-3 align-top">
                @if ($isColor)
                  <div class="flex items-center gap-3">
                    <input type="color"
                           name="{{ $row['key'] }}"
                           value="{{ $inputValue }}"
                           class="w-12 h-10 rounded-lg border border-gray-300 cursor-pointer">
                    <code class="text-xs text-gray-600">{{ $inputValue }}</code>
                  </div>
                @else
                  <input type="{{ $inputType }}"
                         name="{{ $row['key'] }}"
                         value="{{ $inputValue }}"
                         placeholder="{{ $placeholder }}"
                         class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-navy/30 focus:border-navy outline-none">
                @endif
                @if ($row['source'] === 'database')
                  <div class="text-xs text-green-700 mt-1">
                    overriding default ({{ $placeholder !== null && $placeholder !== '' ? $placeholder : 'empty' }})
                  </div>
                @else
                  <div class="text-xs text-gray-400 mt-1">
                    using default: {{ $placeholder !== null && $placeholder !== '' ? $placeholder : 'none' }}
                  </div>
                @endif
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    </form>
  @endforeach

</div>
@endsection

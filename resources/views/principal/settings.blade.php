@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
<div class="p-4 md:p-6 max-w-4xl mx-auto">

  <div class="mb-6">
    <h1 class="text-2xl font-bold text-navy">Settings</h1>
    <p class="text-sm text-gray-600 mt-1">
      School-wide settings. Values stored here override the Skuli360 defaults.
    </p>
  </div>

  <div class="mb-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-sm px-4 py-3">
    Read-only preview. Editing will be enabled in the next step.
  </div>

  @foreach ($rows as $groupName => $items)
    <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
        <div class="font-semibold text-navy">{{ $groupName }}</div>
      </div>
      <table class="w-full text-sm">
        <tbody>
        @foreach ($items as $row)
          <tr class="border-b border-gray-100 last:border-b-0">
            <td class="px-4 py-3 align-top w-1/2">
              <div class="font-medium text-gray-900">
                {{ ucwords(str_replace('_', ' ', $row['key'])) }}
              </div>
              <div class="text-xs text-gray-500 font-mono">{{ $row['key'] }}</div>
            </td>
            <td class="px-4 py-3 align-top">
              @if ($row['effective'] === null || $row['effective'] === '')
                <span class="text-gray-400 italic">(empty)</span>
              @else
                <span class="font-mono text-xs break-all">{{ $row['effective'] }}</span>
              @endif
              @if ($row['source'] === 'database')
                <div class="text-xs text-green-700 mt-1">from database</div>
              @else
                <div class="text-xs text-gray-400 mt-1">using default</div>
              @endif
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  @endforeach

</div>
@endsection

{{--
 | <x-chart id="feesTrend" type="line" :labels="$labels" :datasets="$datasets" />
 |
 | Chart.js wrapper. Canvas + init script.
 |
 | Props:
 |   id            (string, required) canvas element id
 |   type          (line|bar|doughnut|pie, default line)
 |   labels        (array, required) chart x-axis labels
 |   datasets      (array, required) Chart.js datasets (without colors is fine)
 |   options       (array, optional) merged into Chart.js options
 |   height        (int, default 260) fixed canvas container height in px
 |   emptyMessage  (string, default 'No data yet')
 |
 | Behaviour:
 |   - Reads theme colours at runtime via CSS variables (light + dark aware)
 |   - window.skuliChartColors() exposes the palette for callers that want it
 |   - Destroys a prior Chart instance on the same canvas (safe re-render)
 |   - Respects prefers-reduced-motion (disables animation)
 |   - If $labels is empty, renders an empty state instead of a blank canvas
 |
 | Push timing: script is @push('scripts'), so Chart.js (loaded in <head>)
 | and the canvas element are both ready by the time it runs.
--}}
@props([
    'id',
    'type' => 'line',
    'labels' => [],
    'datasets' => [],
    'options' => [],
    'height' => 260,
    'emptyMessage' => 'No data yet',
])

@php
    $isEmpty = empty($labels) || empty($datasets);
@endphp

@if ($isEmpty)
    <div class="flex items-center justify-center text-center text-mutedtext border border-dashed bordersoft rounded-xl"
         style="height: {{ $height }}px;">
        <div>
            <x-icon name="chart-bar" size="lg" class="mb-2 opacity-50" />
            <div class="text-sm">{{ $emptyMessage }}</div>
        </div>
    </div>
@else
    <div style="height: {{ $height }}px;">
        <canvas id="{{ $id }}"></canvas>
    </div>

    @push('scripts')
    <script>
    (function () {
        if (!window.skuliChartColors) {
            window.skuliChartColors = function () {
                var s = getComputedStyle(document.documentElement);
                var get = function (n) { return 'rgb(' + s.getPropertyValue(n).trim().replace(/ +/g, ',') + ')'; };
                return {
                    primary: get('--c-primary-500'),
                    primaryDark: get('--c-primary-700'),
                    accent: get('--c-accent'),
                    success: get('--c-success'),
                    warning: get('--c-warning'),
                    danger: get('--c-danger'),
                    info: get('--c-info'),
                    text: get('--c-text'),
                    muted: get('--c-muted'),
                    border: get('--c-border')
                };
            };
        }
        var canvas = document.getElementById(@json($id));
        if (!canvas || !window.Chart) return;
        if (Chart.getChart(canvas)) Chart.getChart(canvas).destroy();

        var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var cfg = {
            type: @json($type),
            data: { labels: @json($labels), datasets: @json($datasets) },
            options: @json($options)
        };
        cfg.options = cfg.options || {};
        cfg.options.responsive = true;
        cfg.options.maintainAspectRatio = false;
        if (reduceMotion) {
            cfg.options.animation = false;
        } else if (!cfg.options.animation) {
            cfg.options.animation = { duration: 600, easing: 'easeOutQuart' };
        }
        new Chart(canvas, cfg);
    })();
    </script>
    @endpush
@endif

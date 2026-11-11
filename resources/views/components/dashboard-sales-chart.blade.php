@props(['title', 'labels', 'values', 'labelInterval' => 1, 'rotateLabels' => true])

@php
    $width = 1200;
    $height = 330;
    $left = 82;
    $right = 24;
    $top = 24;
    $bottom = 75;
    $plotWidth = $width - $left - $right;
    $plotHeight = $height - $top - $bottom;
    $targetStep = max(max($values ?: [0]) / 4, 0.25);
    $magnitude = 10 ** floor(log10($targetStep));
    $normalizedStep = $targetStep / $magnitude;
    $niceStep = match (true) {
        $normalizedStep <= 1 => 1,
        $normalizedStep <= 2 => 2,
        $normalizedStep <= 5 => 5,
        default => 10,
    };
    $axisStep = $niceStep * $magnitude;
    $axisMaximum = $axisStep * 4;
    $points = collect($values)->values()->map(function ($value, $index) use ($labels, $left, $top, $plotWidth, $plotHeight, $axisMaximum): array {
        $x = $left + ($index * $plotWidth / max(count($labels) - 1, 1));
        $y = $top + $plotHeight - ((float) $value / $axisMaximum) * $plotHeight;

        return ['x' => $x, 'y' => $y, 'value' => (float) $value, 'label' => $labels[$index] ?? ''];
    })->all();
    $linePoints = collect($points)->map(fn (array $point): string => $point['x'].','.$point['y'])->implode(' ');
@endphp

<article class="dashboard-sales-chart">
    <div class="dashboard-sales-chart-heading">
        <span class="sales-chart-icon" aria-hidden="true">↗</span>
        <h2>{{ $title }}</h2>
    </div>
    <div class="sales-chart-scroll">
        <svg class="sales-chart-svg" viewBox="0 0 {{ $width }} {{ $height }}" role="img" aria-label="{{ $title }} line chart showing sales in KSh">
            @for ($tick = 0; $tick <= 4; $tick++)
                @php
                    $tickValue = $axisStep * $tick;
                    $tickY = $top + $plotHeight - ($tick * $plotHeight / 4);
                    $tickLabel = $tickValue >= 1000000
                        ? rtrim(rtrim(number_format($tickValue / 1000000, 1), '0'), '.').'m'
                        : ($tickValue >= 1000 ? rtrim(rtrim(number_format($tickValue / 1000, 1), '0'), '.').'k' : number_format($tickValue, 0));
                @endphp
                <line x1="{{ $left }}" y1="{{ $tickY }}" x2="{{ $width - $right }}" y2="{{ $tickY }}" class="sales-chart-gridline" />
                <text x="{{ $left - 12 }}" y="{{ $tickY + 4 }}" text-anchor="end" class="sales-chart-axis-label">{{ $tickLabel }}</text>
            @endfor
            <text transform="translate(20 {{ $top + ($plotHeight / 2) }}) rotate(-90)" text-anchor="middle" class="sales-chart-axis-title">Total sales (KSh)</text>
            @if (count($points) > 1)
                <polyline points="{{ $linePoints }}" class="sales-chart-line" />
            @endif
            @foreach ($points as $index => $point)
                <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="3" class="sales-chart-point">
                    <title>{{ $point['label'] }}: KSh {{ number_format($point['value'], 2) }}</title>
                </circle>
                @if ($index % $labelInterval === 0 || $index === count($points) - 1)
                    <text @if ($rotateLabels) transform="translate({{ $point['x'] }} {{ $top + $plotHeight + 24 }}) rotate(-35)" text-anchor="end" @else x="{{ $point['x'] }}" y="{{ $top + $plotHeight + 24 }}" text-anchor="middle" @endif class="sales-chart-date-label">{{ $point['label'] }}</text>
                @endif
            @endforeach
        </svg>
    </div>
    @if (max($values ?: [0]) <= 0)
        <p class="sales-chart-empty-note">No sales data for this period yet.</p>
    @endif
</article>

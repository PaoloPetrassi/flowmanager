<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Report') }} - {{ $label }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111827; margin: 28px; font-size: 11px; }
        h1 { margin: 0 0 4px; font-size: 22px; }
        p { margin: 0 0 18px; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px 7px; text-align: left; vertical-align: top; }
        th { background: #eef2f7; font-size: 10px; text-transform: uppercase; }
        .toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; }
        .toolbar button { border: 0; border-radius: 6px; padding: 9px 14px; background: #2563eb; color: white; cursor: pointer; }
        @media print { .toolbar button { display: none; } body { margin: 0; } }
    </style>
</head>
<body>
    <div class="toolbar">
        <div>
            <h1>FlowManager · {{ $label }}</h1>
            <p>
                {{ __('Generated at :date', ['date' => now()->format('d/m/Y H:i')]) }}
                @if ($dateFrom || $dateTo)
                    · {{ __('Period') }}: {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : '—' }} – {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : '—' }}
                @endif
            </p>
        </div>
        <button type="button" onclick="window.print()">{{ __('Print / Save PDF') }}</button>
    </div>

    <table>
        <thead><tr>@foreach ($columns as $label)<th>{{ $label }}</th>@endforeach</tr></thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>@foreach (array_keys($columns) as $key)<td>{{ $row[$key] ?: '—' }}</td>@endforeach</tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

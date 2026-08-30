@extends('layouts.app')

@section('title', __('Reports'))
@section('page-title', __('Reports'))
@section('page-subtitle', __('Operational analysis and exportable datasets'))

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg">
            <div class="card fm-card h-100"><div class="card-body"><div class="text-secondary small">{{ __('Active projects') }}</div><div class="fm-report-kpi">{{ $summary['projects_active'] }}</div></div></div>
        </div>
        <div class="col-6 col-lg">
            <div class="card fm-card h-100"><div class="card-body"><div class="text-secondary small">{{ __('Open tasks') }}</div><div class="fm-report-kpi">{{ $summary['tasks_open'] }}</div></div></div>
        </div>
        <div class="col-6 col-lg">
            <div class="card fm-card h-100"><div class="card-body"><div class="text-secondary small">{{ __('Overdue tasks') }}</div><div class="fm-report-kpi">{{ $summary['tasks_overdue'] }}</div></div></div>
        </div>
        <div class="col-6 col-lg">
            <div class="card fm-card h-100"><div class="card-body"><div class="text-secondary small">{{ __('Open tickets') }}</div><div class="fm-report-kpi">{{ $summary['tickets_open'] }}</div></div></div>
        </div>
        <div class="col-6 col-lg">
            <div class="card fm-card h-100"><div class="card-body"><div class="text-secondary small">{{ __('Assets in service') }}</div><div class="fm-report-kpi">{{ $summary['assets_active'] }}</div></div></div>
        </div>
    </div>

    <div class="card fm-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="report-type" class="form-label">{{ __('Dataset') }}</label>
                    <select id="report-type" name="type" class="form-select">
                        @foreach ($types as $reportType)
                            <option value="{{ $reportType }}" @selected($type === $reportType)>{{ __(ucfirst($reportType)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label for="date-from" class="form-label">{{ __('Created from') }}</label>
                    <input id="date-from" type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                </div>
                <div class="col-6 col-md-3">
                    <label for="date-to" class="form-label">{{ __('Created to') }}</label>
                    <input id="date-to" type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
                </div>
                <div class="col-12 col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>{{ __('Apply') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card fm-card">
        <div class="card-header fm-card-header flex-wrap gap-3">
            <div>
                <h2 class="fm-card-title">{{ __(ucfirst($type)) }}</h2>
                <p class="fm-card-subtitle">{{ trans_choice('ui.report_rows', $totalRows, ['count' => $totalRows]) }}</p>
            </div>
            @if (auth()->user()->hasPermission('reports.export'))
                @php($query = ['type' => $type, 'date_from' => $dateFrom, 'date_to' => $dateTo])
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('reports.csv', $query) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-filetype-csv me-1"></i>CSV</a>
                    <a href="{{ route('reports.excel', $query) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
                    <a href="{{ route('reports.pdf', $query) }}" class="btn btn-sm btn-outline-danger"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
                    <a href="{{ route('reports.print', $query) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-printer me-1"></i>{{ __('Print') }}</a>
                </div>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table fm-table align-middle mb-0">
                <thead>
                    <tr>
                        @foreach ($columns as $label)
                            <th>{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            @foreach (array_keys($columns) as $key)
                                <td>{{ $row[$key] ?: '—' }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($columns) }}" class="text-center py-5 text-secondary">{{ __('No data matches the selected filters.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($totalRows > 200)
            <div class="card-footer bg-white text-secondary small">{{ __('The on-screen preview is limited to 200 rows. Exports include the complete dataset.') }}</div>
        @endif
    </div>
@endsection

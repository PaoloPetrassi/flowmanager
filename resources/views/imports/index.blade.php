@extends('layouts.app')

@section('title', __('Import data'))
@section('page-title', __('Import data'))
@section('page-subtitle', __('Guided CSV/XLSX import with preview, mapping and background processing'))

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-xl-5">
            <div class="card fm-card h-100">
                <div class="card-body">
                    <form method="POST" action="{{ route('imports.preview') }}" enctype="multipart/form-data">
                        @csrf

                        <h5>{{ __('Start import') }}</h5>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Destination') }}</label>
                            <select name="resource_type" class="form-select">
                                @foreach (array_keys($resources) as $resource)
                                    <option value="{{ $resource }}">{{ __(ucfirst($resource)) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('CSV or XLSX') }}</label>
                            <input
                                type="file"
                                name="file"
                                accept=".csv,.xlsx,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                                required
                                class="form-control"
                            >
                        </div>

                        <button class="btn btn-primary">
                            <i class="bi bi-upload me-1"></i>{{ __('Preview import') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="card fm-card h-100">
                <div class="card-header bg-transparent fw-semibold">{{ __('Background imports') }}</div>
                <div class="table-responsive">
                    <table class="table fm-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Job') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th style="min-width: 170px">{{ __('Progress') }}</th>
                                <th>{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($backgroundImports as $job)
                                @php
                                    $statusClass = match ($job->status) {
                                        'completed' => 'text-bg-success',
                                        'failed' => 'text-bg-danger',
                                        'processing' => 'text-bg-primary',
                                        default => 'text-bg-secondary',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $job->name }}</div>
                                        @if ($job->error)
                                            <div class="small text-danger text-break mt-1">{{ $job->error }}</div>
                                        @elseif ($job->message)
                                            <div class="small text-secondary mt-1">{{ $job->message }}</div>
                                        @endif
                                    </td>
                                    <td><span class="badge {{ $statusClass }}">{{ __(ucfirst($job->status)) }}</span></td>
                                    <td>
                                        <div class="progress" role="progressbar" aria-valuenow="{{ $job->progress }}" aria-valuemin="0" aria-valuemax="100">
                                            <div class="progress-bar" style="width: {{ $job->progress }}%">{{ $job->progress }}%</div>
                                        </div>
                                    </td>
                                    <td>{{ $job->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-secondary">
                                        {{ __('No background imports yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card fm-card">
        <div class="card-header bg-transparent fw-semibold">{{ __('Completed import history') }}</div>
        <div class="table-responsive">
            <table class="table fm-table mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Module') }}</th>
                        <th>{{ __('Rows') }}</th>
                        <th>{{ __('Imported') }}</th>
                        <th>{{ __('Failed') }}</th>
                        <th>{{ __('Date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($runs as $run)
                        <tr>
                            <td>{{ __(ucfirst($run->resource_type)) }}</td>
                            <td>{{ $run->total_rows }}</td>
                            <td class="text-success">{{ $run->imported_rows }}</td>
                            <td class="text-danger">{{ $run->failed_rows }}</td>
                            <td>{{ $run->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-secondary">
                                {{ __('No imports executed.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

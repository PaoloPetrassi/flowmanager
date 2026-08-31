@extends('layouts.app')

@section('title', __('Import preview'))
@section('page-title', __('Import preview'))
@section('page-subtitle', __('Map source columns to FlowManager fields before importing'))

@section('content')
    <form method="POST" action="{{ route('imports.store') }}">
        @csrf
        <input type="hidden" name="resource_type" value="{{ $resource }}">
        <input type="hidden" name="stored_path" value="{{ $storedPath }}">
        <input type="hidden" name="original_filename" value="{{ $originalFilename }}">

        <div class="card fm-card mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div>
                        <h5 class="mb-1">{{ __('Column mapping') }}</h5>
                        <div class="small text-secondary">{{ $originalFilename }}</div>
                    </div>
                    <span class="badge text-bg-light border">{{ __(ucfirst($resource)) }}</span>
                </div>

                <div class="row g-3">
                    @foreach ($fields as $field)
                        <div class="col-md-4">
                            <label class="form-label">{{ $field }}</label>
                            <select name="mapping[{{ $field }}]" class="form-select">
                                <option value="">—</option>
                                @foreach ($preview['headers'] as $header)
                                    <option
                                        value="{{ $header }}"
                                        @selected(strtolower($header) === strtolower($field))
                                    >
                                        {{ $header }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card fm-card mb-4">
            <div class="card-header bg-transparent fw-semibold">{{ __('Preview') }}</div>
            <div class="table-responsive">
                <table class="table fm-table mb-0">
                    <thead>
                        <tr>
                            @foreach ($preview['headers'] as $header)
                                <th>{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($preview['rows'] as $row)
                            <tr>
                                @foreach ($preview['headers'] as $header)
                                    <td>{{ $row[$header] ?? '' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="alert alert-info d-flex align-items-start gap-2">
            <i class="bi bi-info-circle mt-1"></i>
            <div>{{ __('The import will run in the background. You can continue using FlowManager while it is processed.') }}</div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('imports.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
            <button class="btn btn-primary">
                <i class="bi bi-play-fill me-1"></i>{{ __('Queue import') }}
            </button>
        </div>
    </form>
@endsection

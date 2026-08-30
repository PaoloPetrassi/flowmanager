@extends('layouts.app')

@section('title', __('Edit Company'))

@section('page-title', __('Edit Company'))

@section('page-subtitle')
    {{ $company->name }}
@endsection

@section('content')

    <form
        method="POST"
        action="{{ route('companies.update', $company) }}"
    >
        @csrf
        @method('PUT')

        <div class="d-flex justify-content-between align-items-center mb-4">

            <a
                href="{{ route('companies.show', $company) }}"
                class="btn btn-outline-secondary"
            >
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('Back') }}
            </a>

            <button
                type="submit"
                class="btn btn-primary"
            >
                <i class="bi bi-check-lg me-1"></i>
                {{ __('Save changes') }}
            </button>

        </div>

        @include('companies._form')

        <div class="d-flex justify-content-end mt-4">

            <button
                type="submit"
                class="btn btn-primary px-4"
            >
                <i class="bi bi-check-lg me-1"></i>
                {{ __('Save changes') }}
            </button>

        </div>

    </form>

@endsection
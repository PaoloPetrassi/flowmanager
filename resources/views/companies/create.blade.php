@extends('layouts.app')

@section('title', 'New Company')

@section('page-title', 'New Company')

@section('page-subtitle')
    Create a new company record
@endsection

@section('content')

    <form
        method="POST"
        action="{{ route('companies.store') }}"
    >
        @csrf

        <div class="d-flex justify-content-between align-items-center mb-4">

            <a
                href="{{ route('companies.index') }}"
                class="btn btn-outline-secondary"
            >
                <i class="bi bi-arrow-left me-1"></i>
                Back
            </a>

            <button
                type="submit"
                class="btn btn-primary"
            >
                <i class="bi bi-check-lg me-1"></i>
                Create company
            </button>

        </div>

        @include('companies._form')

        <div class="d-flex justify-content-end mt-4">

            <button
                type="submit"
                class="btn btn-primary px-4"
            >
                <i class="bi bi-check-lg me-1"></i>
                Create company
            </button>

        </div>

    </form>

@endsection
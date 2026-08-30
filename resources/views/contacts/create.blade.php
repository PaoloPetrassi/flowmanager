@extends('layouts.app')

@section('title', __('New Contact'))

@section('page-title', __('New Contact'))

@section('page-subtitle', __('Create a new CRM contact'))

@section('content')

    <form
        method="POST"
        action="{{ route('contacts.store') }}"
    >
        @csrf

        <div class="d-flex justify-content-between align-items-center mb-4">

            <a
                href="{{ route('contacts.index') }}"
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
                {{ __('Create contact') }}
            </button>

        </div>

        @include('contacts._form')

        <div class="d-flex justify-content-end mt-4">

            <button
                type="submit"
                class="btn btn-primary px-4"
            >
                <i class="bi bi-check-lg me-1"></i>
                {{ __('Create contact') }}
            </button>

        </div>

    </form>

@endsection

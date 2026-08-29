@extends('layouts.app')

@section('title', 'Edit Contact')

@section('page-title', 'Edit Contact')

@section('page-subtitle')
    {{ $contact->full_name }}
@endsection

@section('content')

    <form
        method="POST"
        action="{{ route('contacts.update', $contact) }}"
    >
        @csrf
        @method('PUT')

        <div class="d-flex justify-content-between align-items-center mb-4">

            <a
                href="{{ route('contacts.show', $contact) }}"
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
                Save changes
            </button>

        </div>

        @include('contacts._form')

        <div class="d-flex justify-content-end mt-4">

            <button
                type="submit"
                class="btn btn-primary px-4"
            >
                <i class="bi bi-check-lg me-1"></i>
                Save changes
            </button>

        </div>

    </form>

@endsection

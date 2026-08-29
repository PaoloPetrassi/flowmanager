@extends('layouts.app')

@section('title', 'Dashboard')

@section('page-title', 'Dashboard')

@section('page-subtitle')
    Overview of your FlowManager environment
@endsection

@section('content')

    <div class="row g-4 mb-4">

        @foreach ($stats as $stat)

            <div class="col-12 col-sm-6 col-xl-3">

                <div class="card fm-card fm-stat-card h-100">

                    <div class="card-body">

                        <div class="fm-stat-header">

                            <div class="fm-stat-icon">
                                <i class="bi {{ $stat['icon'] }}"></i>
                            </div>

                            <span class="fm-stat-label">
                                {{ $stat['label'] }}
                            </span>

                        </div>

                        <div class="fm-stat-value">
                            {{ $stat['value'] }}
                        </div>

                    </div>

                </div>

            </div>

        @endforeach

    </div>

    <div class="row g-4">

        <div class="col-12 col-xl-8">

            <div class="card fm-card h-100">

                <div class="card-header fm-card-header">

                    <div>
                        <h2 class="fm-card-title">
                            Role distribution
                        </h2>

                        <p class="fm-card-subtitle">
                            Roles currently available in the application
                        </p>
                    </div>

                </div>

                <div class="table-responsive">

                    <table class="table align-middle mb-0 fm-table">

                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Description</th>
                                <th>Users</th>
                                <th>Type</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse ($roles as $role)

                                <tr>

                                    <td>
                                        <div class="fw-semibold">
                                            {{ $role->name }}
                                        </div>

                                        <small class="text-secondary">
                                            {{ $role->slug }}
                                        </small>
                                    </td>

                                    <td>
                                        {{ $role->description ?: '—' }}
                                    </td>

                                    <td>
                                        {{ $role->users_count }}
                                    </td>

                                    <td>

                                        @if ($role->is_system)

                                            <span class="badge text-bg-primary">
                                                System
                                            </span>

                                        @else

                                            <span class="badge text-bg-secondary">
                                                Custom
                                            </span>

                                        @endif

                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td
                                        colspan="4"
                                        class="text-center py-4 text-secondary"
                                    >
                                        No roles available.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

        <div class="col-12 col-xl-4">

            <div class="card fm-card mb-4">

                <div class="card-body">

                    <h2 class="fm-card-title mb-3">
                        Your access
                    </h2>

                    <div class="fm-access-user">

                        <div class="fm-access-avatar">
                            {{ strtoupper(substr($currentUser->name, 0, 1)) }}
                        </div>

                        <div>
                            <div class="fw-semibold">
                                {{ $currentUser->name }}
                            </div>

                            <small class="text-secondary">
                                {{ $currentUser->email }}
                            </small>
                        </div>

                    </div>

                    <hr>

                    <div class="fm-access-row">
                        <span>Roles</span>

                        <strong>
                            {{ $currentUser->roles->count() }}
                        </strong>
                    </div>

                    <div class="fm-access-row">
                        <span>Permissions</span>

                        <strong>
                            {{ $permissionCount }}
                        </strong>
                    </div>

                    <div class="mt-3">

                        @foreach ($currentUser->roles as $role)

                            <span class="badge text-bg-primary me-1">
                                {{ $role->name }}
                            </span>

                        @endforeach

                    </div>

                </div>

            </div>

            <div class="card fm-card">

                <div class="card-header fm-card-header">

                    <div>
                        <h2 class="fm-card-title">
                            Recent users
                        </h2>

                        <p class="fm-card-subtitle">
                            Latest accounts
                        </p>
                    </div>

                </div>

                <div class="list-group list-group-flush">

                    @forelse ($latestUsers as $user)

                        <div class="list-group-item fm-user-list-item">

                            <div class="fm-small-avatar">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>

                            <div class="flex-grow-1">

                                <div class="fw-semibold">
                                    {{ $user->name }}
                                </div>

                                <small class="text-secondary">
                                    {{ $user->roles->pluck('name')->implode(', ') ?: 'No role' }}
                                </small>

                            </div>

                        </div>

                    @empty

                        <div class="p-4 text-center text-secondary">
                            No users found.
                        </div>

                    @endforelse

                </div>

            </div>

        </div>

    </div>

@endsection
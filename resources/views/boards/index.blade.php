@extends('layouts.app')

@section('title', __('Kanban'))
@section('page-title', __('Kanban'))
@section('page-subtitle', __('Visual workflow for tasks and support tickets'))

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div class="btn-group" role="group" aria-label="{{ __('Board type') }}">
            @can('viewAny', App\Models\Task::class)
                <a href="{{ route('boards.index', ['type' => 'tasks']) }}" class="btn {{ $type === 'tasks' ? 'btn-primary' : 'btn-outline-primary' }}">{{ __('Tasks') }}</a>
            @endcan
            @can('viewAny', App\Models\Ticket::class)
                <a href="{{ route('boards.index', ['type' => 'tickets']) }}" class="btn {{ $type === 'tickets' ? 'btn-primary' : 'btn-outline-primary' }}">{{ __('Tickets') }}</a>
            @endcan
        </div>

        <div class="d-flex align-items-center flex-wrap gap-2">
            <div class="btn-group btn-group-sm" role="group" aria-label="{{ __('Assignment filter') }}">
                <a href="{{ route('boards.index', ['type' => $type]) }}" class="btn {{ $assigneeId ? 'btn-outline-secondary' : 'btn-secondary' }}">{{ __('All') }}</a>
                <a href="{{ route('boards.index', ['type' => $type, 'assigned_to' => auth()->id()]) }}" class="btn {{ $assigneeId === auth()->id() ? 'btn-secondary' : 'btn-outline-secondary' }}">{{ __('My work') }}</a>
            </div>
            <div class="text-secondary small">
                <i class="bi bi-arrows-move me-1"></i>{{ $canUpdate ? __('Drag cards between columns or use the status selector.') : __('Read-only board.') }}
            </div>
        </div>
    </div>

    <div class="fm-kanban-scroll">
        <div class="fm-kanban" data-kanban-board data-kanban-enabled="{{ $canUpdate ? '1' : '0' }}">
            @foreach ($columns as $status => $statusLabel)
                @php
                    $columnItems = $items->get($status, collect());
                @endphp
                <section class="fm-kanban-column" data-kanban-status="{{ $status }}">
                    <header class="fm-kanban-column-header">
                        <span>{{ $statusLabel }}</span>
                        <span class="fm-kanban-count" data-kanban-count>{{ $columnItems->count() }}</span>
                    </header>

                    <div class="fm-kanban-list" data-kanban-list>
                        @foreach ($columnItems as $item)
                            @php
                                $isTask = $type === 'tasks';
                                $updateUrl = $isTask
                                    ? route('boards.tasks.status', $item)
                                    : route('boards.tickets.status', $item);
                                $showUrl = $isTask
                                    ? route('tasks.show', $item)
                                    : route('tickets.show', $item);
                            @endphp
                            <article
                                class="fm-kanban-card"
                                draggable="{{ $canUpdate ? 'true' : 'false' }}"
                                data-kanban-card
                                data-update-url="{{ $updateUrl }}"
                            >
                                <div class="d-flex justify-content-between gap-2 align-items-start">
                                    <a href="{{ $showUrl }}" class="fw-semibold text-dark text-decoration-none">{{ $isTask ? $item->title : $item->subject }}</a>
                                    <span class="badge {{ in_array($item->priority->value, ['urgent', 'high'], true) ? 'text-bg-warning' : 'text-bg-light' }}">{{ $item->priority->label() }}</span>
                                </div>
                                <div class="small text-secondary mt-2">
                                    @if ($isTask)
                                        {{ $item->project?->code }} · {{ $item->project?->name }}
                                    @else
                                        {{ $item->reference }}@if($item->company) · {{ $item->company->name }}@endif
                                    @endif
                                </div>
                                @if ($isTask && $item->due_date)
                                    <div class="small mt-2 {{ $item->due_date->isPast() && $item->status->value !== 'completed' ? 'text-danger' : 'text-secondary' }}">
                                        <i class="bi bi-calendar3 me-1"></i>{{ $item->due_date->format('d/m/Y') }}
                                    </div>
                                @endif
                                @if ($item->assignee)
                                    <div class="fm-kanban-assignee mt-2"><span>{{ strtoupper(substr($item->assignee->name, 0, 1)) }}</span>{{ $item->assignee->name }}</div>
                                @endif

                                @if ($canUpdate)
                                    <form method="POST" action="{{ $updateUrl }}" class="fm-kanban-status-form mt-3" data-kanban-status-form>
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="form-select form-select-sm" aria-label="{{ __('Status') }}">
                                            @foreach ($columns as $optionStatus => $optionLabel)
                                                <option value="{{ $optionStatus }}" @selected($optionStatus === $status)>{{ $optionLabel }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">{{ __('Move') }}</button>
                                    </form>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>
@endsection

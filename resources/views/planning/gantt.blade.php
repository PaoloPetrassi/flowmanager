@extends('layouts.app')

@section('title', __('Gantt'))
@section('page-title', __('Gantt'))
@section('page-subtitle', __('Project and task schedule across a shared timeline'))

@section('content')
    <div class="card fm-card mb-4"><div class="card-body"><form class="row g-3 align-items-end" method="GET">
        <div class="col-12 col-sm-4"><label class="form-label">{{ __('From') }}</label><input class="form-control" type="date" name="start" value="{{ $start->format('Y-m-d') }}"></div>
        <div class="col-12 col-sm-4"><label class="form-label">{{ __('To') }}</label><input class="form-control" type="date" name="end" value="{{ $end->format('Y-m-d') }}"></div>
        <div class="col-12 col-sm-4 d-flex gap-2"><button class="btn btn-primary flex-grow-1">{{ __('Apply') }}</button><a class="btn btn-outline-secondary" href="{{ route('planning.gantt') }}">{{ __('Reset') }}</a></div>
    </form></div></div>

    <div class="card fm-card">
        <div class="card-header fm-card-header"><div><h2 class="fm-card-title">{{ __('Timeline') }}</h2><p class="fm-card-subtitle">{{ $start->format('d/m/Y') }} — {{ $end->format('d/m/Y') }}</p></div><span class="badge text-bg-light">{{ $projects->count() }} {{ __('projects') }}</span></div>
        <div class="card-body p-0">
            <div class="fm-gantt-scroll">
                <div class="fm-gantt" style="--fm-gantt-days: {{ $totalDays }};">
                    <div class="fm-gantt-axis"><div class="fm-gantt-label">{{ __('Project / task') }}</div><div class="fm-gantt-track"><span>{{ $start->format('d M') }}</span><span>{{ $start->copy()->addDays((int) ($totalDays / 2))->format('d M') }}</span><span>{{ $end->format('d M') }}</span></div></div>
                    @forelse ($projects as $project)
                        @php
                            $projectStart = $project->start_date ?: $start;
                            $projectEnd = $project->due_date ?: $projectStart;
                            $visibleStart = $projectStart->lt($start) ? $start : $projectStart;
                            $visibleEnd = $projectEnd->gt($end) ? $end : $projectEnd;
                            $left = max(0, $start->diffInDays($visibleStart, false) / $totalDays * 100);
                            $width = max(1.5, $visibleStart->diffInDays($visibleEnd) / $totalDays * 100);
                        @endphp
                        <div class="fm-gantt-row is-project"><div class="fm-gantt-label"><a href="{{ route('projects.show', $project) }}" class="fw-semibold">{{ $project->code }}</a><small>{{ $project->name }}</small></div><div class="fm-gantt-track"><a href="{{ route('projects.show', $project) }}" class="fm-gantt-bar is-project" style="left: {{ $left }}%; width: {{ $width }}%;" title="{{ $project->name }}"></a></div></div>
                        @foreach ($project->tasks as $task)
                            @php
                                $taskDate = $task->due_date;
                                $taskLeft = max(0, min(99, $start->diffInDays($taskDate, false) / $totalDays * 100));
                            @endphp
                            @if ($taskDate->betweenIncluded($start, $end))
                                <div class="fm-gantt-row"><div class="fm-gantt-label ps-4"><a href="{{ route('tasks.show', $task) }}">{{ Str::limit($task->title, 48) }}</a></div><div class="fm-gantt-track"><a href="{{ route('tasks.show', $task) }}" class="fm-gantt-marker" style="left: {{ $taskLeft }}%;" title="{{ $task->title }} · {{ $taskDate->format('d/m/Y') }}"></a></div></div>
                            @endif
                        @endforeach
                    @empty
                        <div class="p-5 text-center text-secondary">{{ __('No projects fall within this time range.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

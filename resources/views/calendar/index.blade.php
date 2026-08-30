@extends('layouts.app')

@section('title', __('Calendar'))
@section('page-title', __('Calendar'))
@section('page-subtitle', __('Projects, tasks and asset warranty deadlines in one place'))

@section('content')
    <div class="card fm-card">
        <div class="card-header fm-card-header flex-wrap gap-3">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('calendar.index', ['month' => $previousMonth]) }}" class="btn btn-sm btn-outline-secondary" aria-label="{{ __('Previous month') }}"><i class="bi bi-chevron-left"></i></a>
                <div>
                    <h2 class="fm-card-title text-capitalize">{{ $month->locale(app()->getLocale())->translatedFormat('F Y') }}</h2>
                    <p class="fm-card-subtitle">{{ trans_choice('ui.calendar_events', $eventCount, ['count' => $eventCount]) }}</p>
                </div>
                <a href="{{ route('calendar.index', ['month' => $nextMonth]) }}" class="btn btn-sm btn-outline-secondary" aria-label="{{ __('Next month') }}"><i class="bi bi-chevron-right"></i></a>
            </div>
            <a href="{{ route('calendar.index', ['month' => now()->format('Y-m')]) }}" class="btn btn-sm btn-outline-primary">{{ __('Today') }}</a>
        </div>

        <div class="fm-calendar-scroll">
            <div class="fm-calendar" role="grid">
                @foreach ([__('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat'), __('Sun')] as $weekday)
                    <div class="fm-calendar-weekday" role="columnheader">{{ $weekday }}</div>
                @endforeach

                @foreach ($days as $day)
                    @php($dateKey = $day->toDateString())
                    @php($dayEvents = $eventsByDate->get($dateKey, collect()))
                    <div class="fm-calendar-day {{ $day->month !== $month->month ? 'is-outside' : '' }} {{ $day->isToday() ? 'is-today' : '' }}" role="gridcell">
                        <div class="fm-calendar-day-number">{{ $day->day }}</div>
                        <div class="fm-calendar-events">
                            @foreach ($dayEvents->take(4) as $event)
                                <a href="{{ $event['url'] }}" class="fm-calendar-event fm-calendar-event-{{ $event['type'] }}" title="{{ $event['label'] }}">
                                    <i class="bi {{ $event['icon'] }}"></i>
                                    <span>{{ $event['label'] }}</span>
                                </a>
                            @endforeach
                            @if ($dayEvents->count() > 4)
                                <span class="fm-calendar-more">+{{ $dayEvents->count() - 4 }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card-footer bg-white d-flex flex-wrap gap-3 small text-secondary">
            <span><i class="fm-legend-dot fm-legend-primary"></i>{{ __('Project deadline') }}</span>
            <span><i class="fm-legend-dot fm-legend-success"></i>{{ __('Task deadline') }}</span>
            <span><i class="fm-legend-dot fm-legend-warning"></i>{{ __('Warranty expiry') }}</span>
        </div>
    </div>
@endsection

@php
    $timelineGroups = $activityLogs->groupBy(fn ($log) => $log->created_at->toDateString());
@endphp

<div class="fm-activity-timeline">
    @forelse ($timelineGroups as $date => $logs)
        <div class="fm-timeline-day">
            <div class="fm-timeline-date">
                {{ \Carbon\CarbonImmutable::parse($date)->locale(app()->getLocale())->isToday()
                    ? __('Today')
                    : \Carbon\CarbonImmutable::parse($date)->locale(app()->getLocale())->translatedFormat('d M Y') }}
            </div>

            <div class="fm-timeline-items">
                @foreach ($logs as $log)
                    @php
                        $eventIcon = match ($log->event) {
                            'created' => 'bi-plus-circle',
                            'updated' => 'bi-pencil-square',
                            'deleted', 'permanently_deleted' => 'bi-trash',
                            'restored' => 'bi-arrow-counterclockwise',
                            'commented' => 'bi-chat-left-text',
                            'attachment_added' => 'bi-paperclip',
                            'permissions_updated', 'roles_updated' => 'bi-shield-check',
                            default => 'bi-clock-history',
                        };
                        $subjectUrl = $log->subjectUrl();
                    @endphp

                    <div class="fm-timeline-item">
                        <div class="fm-timeline-marker"><i class="bi {{ $eventIcon }}"></i></div>
                        <div class="fm-timeline-content min-w-0">
                            <div class="small">
                                <strong>{{ $log->user?->name ?: __('System') }}</strong>
                                <span>{{ __(str_replace('_', ' ', $log->event)) }}</span>
                                <span class="text-secondary">{{ $log->resourceLabel() }}</span>
                                @if ($subjectUrl)
                                    <a href="{{ $subjectUrl }}" class="fw-semibold">{{ $log->auditable_label }}</a>
                                @else
                                    <strong>{{ $log->auditable_label }}</strong>
                                @endif
                            </div>
                            <div class="small text-secondary mt-1">{{ $log->created_at->format('H:i') }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="p-4 text-center text-secondary">{{ __('No tracked activity in the selected period.') }}</div>
    @endforelse
</div>

@php
    $resourceModel->loadMissing(['tags', 'customFieldValues.field']);
    $visibleCustomValues = $resourceModel->customFieldValues->filter(fn ($value) => filled($value->value) && $value->field?->is_active);
@endphp
@if ($resourceModel->tags->isNotEmpty() || $visibleCustomValues->isNotEmpty())
<div class="card fm-card mt-4">
    <div class="card-header bg-white"><strong>{{ __('Classification & custom data') }}</strong></div>
    <div class="card-body">
        @if ($resourceModel->tags->isNotEmpty())
            <div class="d-flex gap-2 flex-wrap mb-3">@foreach($resourceModel->tags as $tag)<span class="fm-tag" style="--tag-color:{{ $tag->color ?: '#64748b' }}">{{ $tag->name }}</span>@endforeach</div>
        @endif
        @if ($visibleCustomValues->isNotEmpty())
            <div class="row g-3">@foreach($visibleCustomValues as $customValue)<div class="col-md-4"><div class="small text-secondary">{{ $customValue->field->name }}</div><div class="fw-semibold">{{ $customValue->value }}</div></div>@endforeach</div>
        @endif
    </div>
</div>
@endif

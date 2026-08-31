@php
    $documentResourceType = strtolower(class_basename($resourceModel));
    $documentTemplates = App\Models\DocumentTemplate::query()->where('resource_type', $documentResourceType)->where('is_active', true)->orderBy('name')->get();
@endphp
@if ($documentTemplates->isNotEmpty() && auth()->user()->hasPermission('documents.view'))
<div class="card fm-card mt-4">
    <div class="card-header bg-white"><strong>{{ __('Generate document') }}</strong></div>
    <div class="card-body d-flex gap-2 flex-wrap">
        @foreach($documentTemplates as $template)
            <div class="btn-group btn-group-sm">
                <a class="btn btn-outline-primary" href="{{ route('document-templates.generate', [$template, $documentResourceType, $resourceModel->id, 'format' => 'pdf']) }}"><i class="bi bi-file-earmark-pdf me-1"></i>{{ $template->name }}</a>
                <a class="btn btn-outline-secondary" href="{{ route('document-templates.generate', [$template, $documentResourceType, $resourceModel->id, 'format' => 'doc']) }}" title="{{ __('Word') }}">DOC</a>
            </div>
        @endforeach
    </div>
</div>
@endif

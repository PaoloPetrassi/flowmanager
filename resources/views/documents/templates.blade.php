@extends('layouts.app')
@section('title', __('Document templates'))
@section('page-title', __('Document templates'))
@section('page-subtitle', __('Generate PDF or Word documents from FlowManager records'))
@section('content')
<div class="row g-4">
    <div class="col-12 col-xl-5"><div class="card fm-card"><div class="card-body"><form method="POST" action="{{ route('document-templates.store') }}">@csrf
        <h5>{{ __('New document template') }}</h5>
        <div class="row g-3"><div class="col-md-7"><label class="form-label">{{ __('Name') }}</label><input name="name" class="form-control" required></div><div class="col-md-5"><label class="form-label">{{ __('Module') }}</label><select name="resource_type" class="form-select">@foreach(['company','contact','project','task','asset','ticket'] as $type)<option value="{{ $type }}">{{ __(ucfirst($type)) }}</option>@endforeach</select></div><div class="col-md-4"><label class="form-label">{{ __('Default format') }}</label><select name="default_format" class="form-select"><option value="pdf">PDF</option><option value="doc">Word</option></select></div><div class="col-12"><label class="form-label">{{ __('Template body') }}</label><textarea name="content" rows="12" class="form-control" required placeholder="Project: &#123;&#123;name&#125;&#125;&#10;Code: &#123;&#123;code&#125;&#125;&#10;Company: &#123;&#123;company.name&#125;&#125;"></textarea><div class="form-text">{{ __('Available placeholders include') }} <code>&#123;&#123;name&#125;&#125;</code>, <code>&#123;&#123;code&#125;&#125;</code>, <code>&#123;&#123;company.name&#125;&#125;</code>, <code>&#123;&#123;due_date&#125;&#125;</code>.</div></div></div>
        <button class="btn btn-primary mt-3">{{ __('Create template') }}</button>
    </form></div></div></div>
    <div class="col-12 col-xl-7"><div class="card fm-card"><div class="table-responsive"><table class="table fm-table mb-0"><thead><tr><th>{{ __('Name') }}</th><th>{{ __('Module') }}</th><th>{{ __('Format') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead><tbody>@forelse($templates as $template)<tr><td>{{ $template->name }}</td><td>{{ ucfirst($template->resource_type) }}</td><td>{{ strtoupper($template->default_format) }}</td><td>{{ $template->is_active ? __('Active') : __('Inactive') }}</td><td class="text-end"><form method="POST" action="{{ route('document-templates.destroy',$template) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td></tr>@empty<tr><td colspan="5" class="text-center py-5">{{ __('No document templates configured.') }}</td></tr>@endforelse</tbody></table></div></div></div>
</div>
@endsection

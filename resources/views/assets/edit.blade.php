@extends('layouts.app')
@section('title', 'Edit asset')
@section('page-title', 'Edit asset')
@section('page-subtitle'){{ $asset->asset_tag }} — {{ $asset->name }}@endsection
@section('content')<div class="card fm-card"><form method="POST" action="{{ route('assets.update', $asset) }}">@csrf @method('PUT')<div class="card-body p-4">@include('assets._form')</div><div class="card-footer bg-white d-flex justify-content-end gap-2 p-3"><a href="{{ route('assets.show', $asset) }}" class="btn btn-outline-secondary">Cancel</a><button class="btn btn-primary" type="submit">Save changes</button></div></form></div>@endsection

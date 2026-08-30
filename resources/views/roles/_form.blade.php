@if ($errors->any())
    <div class="alert alert-danger"><div class="fw-semibold mb-2">Please correct the highlighted fields.</div><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="row g-3 mb-4">
    <div class="col-12 col-md-6"><label for="name" class="form-label fw-semibold">Role name</label><input id="name" name="name" maxlength="100" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $role->name) }}" {{ $role->is_system ? 'readonly' : '' }} required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-12 col-md-6"><label for="slug" class="form-label fw-semibold">Slug</label><input id="slug" name="slug" maxlength="100" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $role->slug) }}" {{ $role->is_system ? 'readonly' : '' }} placeholder="Generated from the name if empty"><div class="form-text">Used internally by authorization rules.</div>@error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-12"><label for="description" class="form-label fw-semibold">Description</label><input id="description" name="description" maxlength="255" class="form-control @error('description') is-invalid @enderror" value="{{ old('description', $role->description) }}">@error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3"><div><h2 class="h6 mb-1">Permissions</h2><div class="small text-secondary">Select the capabilities granted by this role.</div></div>@if ($role->is_system)<span class="badge text-bg-primary">System role</span>@endif</div>
<div class="row g-3">
    @foreach ($permissionGroups as $group => $permissions)
        <div class="col-12 col-md-6 col-xl-4"><div class="border rounded h-100 p-3"><div class="fw-semibold text-capitalize mb-3">{{ str_replace('_', ' ', $group) }}</div>
            @foreach ($permissions as $permission)
                <label class="d-flex gap-2 align-items-start mb-2"><input class="form-check-input mt-1" type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked(collect(old('permissions', $selectedPermissions))->contains($permission->id))><span><span class="d-block">{{ $permission->name }}</span><span class="small text-secondary">{{ $permission->slug }}</span></span></label>
            @endforeach
        </div></div>
    @endforeach
</div>
@error('permissions')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
@error('permissions.*')<div class="text-danger small mt-2">{{ $message }}</div>@enderror

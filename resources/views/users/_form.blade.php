@if ($errors->any())
    <div class="alert alert-danger"><div class="fw-semibold mb-2">{{ __('Please correct the highlighted fields.') }}</div><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="row g-3">
    <div class="col-12 col-md-6"><label for="name" class="form-label fw-semibold">{{ __('Name') }}</label><input id="name" name="name" maxlength="255" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-12 col-md-6"><label for="email" class="form-label fw-semibold">{{ __('Email') }}</label><input id="email" name="email" type="email" maxlength="255" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-12 col-md-6"><label for="password" class="form-label fw-semibold">{{ __('Password') }}{{ $user->exists ? ' ('.__('leave blank to keep current').')' : '' }}</label><input id="password" name="password" type="password" autocomplete="new-password" class="form-control @error('password') is-invalid @enderror" {{ $user->exists ? '' : 'required' }}>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-12 col-md-6"><label for="password_confirmation" class="form-label fw-semibold">{{ __('Confirm password') }}</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="form-control" {{ $user->exists ? '' : 'required' }}></div>
    <div class="col-12"><label class="form-label fw-semibold">{{ __('Roles') }}</label><div class="row g-2">
        @foreach ($roles as $role)
            <div class="col-12 col-md-6 col-xl-4"><label class="border rounded p-3 d-flex gap-3 align-items-start w-100"><input class="form-check-input mt-1" type="checkbox" name="roles[]" value="{{ $role->id }}" @checked(collect(old('roles', $selectedRoles))->contains($role->id))><span><span class="fw-semibold d-block">{{ __($role->name) }}</span><span class="small text-secondary">{{ $role->slug }}{{ $role->is_system ? ' · '.__('system role') : '' }}</span></span></label></div>
        @endforeach
    </div>@error('roles')<div class="text-danger small mt-2">{{ $message }}</div>@enderror @error('roles.*')<div class="text-danger small mt-2">{{ $message }}</div>@enderror</div>
</div>

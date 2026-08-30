<div class="row g-4">

    <div class="col-12 col-xl-8">

        <div class="card fm-card">

            <div class="card-header fm-card-header">
                <div>
                    <h2 class="fm-card-title">
                        {{ __('General information') }}
                    </h2>

                    <p class="fm-card-subtitle">
                        {{ __('Contact identity and business role') }}
                    </p>
                </div>
            </div>

            <div class="card-body p-4">

                <div class="row g-3">

                    <div class="col-12 col-md-6">

                        <label
                            for="first_name"
                            class="form-label fw-semibold"
                        >
                            {{ __('First name *') }}
                        </label>

                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            value="{{ old('first_name', $contact->first_name) }}"
                            class="form-control @error('first_name') is-invalid @enderror"
                            required
                            autofocus
                        >

                        @error('first_name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="col-12 col-md-6">

                        <label
                            for="last_name"
                            class="form-label fw-semibold"
                        >
                            {{ __('Last name *') }}
                        </label>

                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            value="{{ old('last_name', $contact->last_name) }}"
                            class="form-control @error('last_name') is-invalid @enderror"
                            required
                        >

                        @error('last_name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="col-12 col-md-6">

                        <label
                            for="job_title"
                            class="form-label fw-semibold"
                        >
                            {{ __('Job title') }}
                        </label>

                        <input
                            type="text"
                            id="job_title"
                            name="job_title"
                            value="{{ old('job_title', $contact->job_title) }}"
                            class="form-control @error('job_title') is-invalid @enderror"
                            placeholder="{{ __('e.g. Chief Financial Officer') }}"
                        >

                        @error('job_title')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="col-12 col-md-6">

                        <label
                            for="department"
                            class="form-label fw-semibold"
                        >
                            {{ __('Department') }}
                        </label>

                        <input
                            type="text"
                            id="department"
                            name="department"
                            value="{{ old('department', $contact->department) }}"
                            class="form-control @error('department') is-invalid @enderror"
                            placeholder="{{ __('e.g. Finance') }}"
                        >

                        @error('department')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

            </div>

        </div>

        <div class="card fm-card mt-4">

            <div class="card-header fm-card-header">
                <div>
                    <h2 class="fm-card-title">
                        {{ __('Contact details') }}
                    </h2>

                    <p class="fm-card-subtitle">
                        {{ __('Email and telephone information') }}
                    </p>
                </div>
            </div>

            <div class="card-body p-4">

                <div class="row g-3">

                    <div class="col-12">

                        <label
                            for="email"
                            class="form-label fw-semibold"
                        >
                            {{ __('Email') }}
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', $contact->email) }}"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="name@company.com"
                        >

                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="col-12 col-md-6">

                        <label
                            for="phone"
                            class="form-label fw-semibold"
                        >
                            {{ __('Phone') }}
                        </label>

                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            value="{{ old('phone', $contact->phone) }}"
                            class="form-control @error('phone') is-invalid @enderror"
                        >

                        @error('phone')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="col-12 col-md-6">

                        <label
                            for="mobile"
                            class="form-label fw-semibold"
                        >
                            {{ __('Mobile') }}
                        </label>

                        <input
                            type="text"
                            id="mobile"
                            name="mobile"
                            value="{{ old('mobile', $contact->mobile) }}"
                            class="form-control @error('mobile') is-invalid @enderror"
                        >

                        @error('mobile')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

            </div>

        </div>

        <div class="card fm-card mt-4">

            <div class="card-header fm-card-header">
                <div>
                    <h2 class="fm-card-title">
                        {{ __('Notes') }}
                    </h2>

                    <p class="fm-card-subtitle">
                        {{ __('Optional internal information about this contact') }}
                    </p>
                </div>
            </div>

            <div class="card-body p-4">

                <label
                    for="notes"
                    class="visually-hidden"
                >
                    {{ __('Notes') }}
                </label>

                <textarea
                    id="notes"
                    name="notes"
                    rows="6"
                    class="form-control @error('notes') is-invalid @enderror"
                >{{ old('notes', $contact->notes) }}</textarea>

                @error('notes')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

            </div>

        </div>

    </div>

    <div class="col-12 col-xl-4">

        <div class="card fm-card">

            <div class="card-header fm-card-header">
                <div>
                    <h2 class="fm-card-title">
                        {{ __('Company') }}
                    </h2>

                    <p class="fm-card-subtitle">
                        {{ __('Associate the contact with a CRM company') }}
                    </p>
                </div>
            </div>

            <div class="card-body p-4">

                <div class="mb-4">

                    <label
                        for="company_id"
                        class="form-label fw-semibold"
                    >
                        {{ __('Company') }}
                    </label>

                    <select
                        id="company_id"
                        name="company_id"
                        class="form-select @error('company_id') is-invalid @enderror"
                    >

                        <option value="">
                            {{ __('No company') }}
                        </option>

                        @foreach ($companies as $company)

                            <option
                                value="{{ $company->id }}"
                                @selected(
                                    (string) old(
                                        'company_id',
                                        $contact->company_id
                                    ) === (string) $company->id
                                )
                            >
                                {{ $company->name }}
                            </option>

                        @endforeach

                    </select>

                    @error('company_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                <div class="form-check">

                    <input
                        type="checkbox"
                        id="is_primary"
                        name="is_primary"
                        value="1"
                        class="form-check-input @error('is_primary') is-invalid @enderror"
                        @checked(
                            old(
                                'is_primary',
                                $contact->is_primary
                            )
                        )
                    >

                    <label
                        for="is_primary"
                        class="form-check-label fw-semibold"
                    >
                        {{ __('Primary contact') }}
                    </label>

                    <div class="form-text">
                        {{ __('A company can have only one primary contact. Selecting this will replace the current primary contact.') }}
                    </div>

                    @error('is_primary')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

            </div>

        </div>

    </div>

</div>

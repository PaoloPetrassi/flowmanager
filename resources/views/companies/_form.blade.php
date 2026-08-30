<div class="row g-4">

    <div class="col-12">

        <div class="card fm-card">

            <div class="card-header fm-card-header">
                <div>
                    <h2 class="fm-card-title">
                        {{ __('General information') }}
                    </h2>

                    <p class="fm-card-subtitle">
                        {{ __('Basic company identification and classification') }}
                    </p>
                </div>
            </div>

            <div class="card-body p-4">

                <div class="row g-3">

                    <div class="col-12 col-lg-6">

                        <label
                            for="name"
                            class="form-label fw-semibold"
                        >
                            {{ __('Company name *') }}
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $company->name) }}"
                            class="form-control @error('name') is-invalid @enderror"
                            required
                        >

                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="col-12 col-lg-6">

                        <label
                            for="legal_name"
                            class="form-label fw-semibold"
                        >
                            {{ __('Legal name') }}
                        </label>

                        <input
                            type="text"
                            id="legal_name"
                            name="legal_name"
                            value="{{ old('legal_name', $company->legal_name) }}"
                            class="form-control @error('legal_name') is-invalid @enderror"
                        >

                        @error('legal_name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="col-12 col-md-6">

                        <label
                            for="type"
                            class="form-label fw-semibold"
                        >
                            {{ __('Type *') }}
                        </label>

                        <select
                            id="type"
                            name="type"
                            class="form-select @error('type') is-invalid @enderror"
                            required
                        >

                            <option value="">
                                {{ __('Select type') }}
                            </option>

                            @foreach ($types as $type)

                                <option
                                    value="{{ $type->value }}"
                                    @selected(
                                        old(
                                            'type',
                                            $company->type?->value
                                        ) === $type->value
                                    )
                                >
                                    {{ $type->label() }}
                                </option>

                            @endforeach

                        </select>

                        @error('type')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="col-12 col-md-6">

                        <label
                            for="status"
                            class="form-label fw-semibold"
                        >
                            {{ __('Status *') }}
                        </label>

                        <select
                            id="status"
                            name="status"
                            class="form-select @error('status') is-invalid @enderror"
                            required
                        >

                            <option value="">
                                {{ __('Select status') }}
                            </option>

                            @foreach ($statuses as $status)

                                <option
                                    value="{{ $status->value }}"
                                    @selected(
                                        old(
                                            'status',
                                            $company->status?->value
                                        ) === $status->value
                                    )
                                >
                                    {{ $status->label() }}
                                </option>

                            @endforeach

                        </select>

                        @error('status')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="col-12 col-md-8">

                        <label
                            for="industry"
                            class="form-label fw-semibold"
                        >
                            {{ __('Industry') }}
                        </label>

                        <input
                            type="text"
                            id="industry"
                            name="industry"
                            value="{{ old('industry', $company->industry) }}"
                            class="form-control @error('industry') is-invalid @enderror"
                        >

                        @error('industry')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="col-12 col-md-4">

                        <label
                            for="employees"
                            class="form-label fw-semibold"
                        >
                            {{ __('Employees') }}
                        </label>

                        <input
                            type="number"
                            id="employees"
                            name="employees"
                            value="{{ old('employees', $company->employees) }}"
                            min="0"
                            class="form-control @error('employees') is-invalid @enderror"
                        >

                        @error('employees')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="col-12 col-xl-6">

        <div class="card fm-card h-100">

            <div class="card-header fm-card-header">

                <div>
                    <h2 class="fm-card-title">
                        {{ __('Fiscal information') }}
                    </h2>

                    <p class="fm-card-subtitle">
                        {{ __('Tax and registration identifiers') }}
                    </p>
                </div>

            </div>

            <div class="card-body p-4">

                <div class="mb-3">

                    <label
                        for="vat_number"
                        class="form-label fw-semibold"
                    >
                        {{ __('VAT number') }}
                    </label>

                    <input
                        type="text"
                        id="vat_number"
                        name="vat_number"
                        value="{{ old('vat_number', $company->vat_number) }}"
                        class="form-control @error('vat_number') is-invalid @enderror"
                    >

                    @error('vat_number')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                <div>

                    <label
                        for="tax_code"
                        class="form-label fw-semibold"
                    >
                        {{ __('Tax code') }}
                    </label>

                    <input
                        type="text"
                        id="tax_code"
                        name="tax_code"
                        value="{{ old('tax_code', $company->tax_code) }}"
                        class="form-control @error('tax_code') is-invalid @enderror"
                    >

                    @error('tax_code')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

            </div>

        </div>

    </div>

    <div class="col-12 col-xl-6">

        <div class="card fm-card h-100">

            <div class="card-header fm-card-header">

                <div>
                    <h2 class="fm-card-title">
                        {{ __('Contact information') }}
                    </h2>

                    <p class="fm-card-subtitle">
                        {{ __('Main company communication channels') }}
                    </p>
                </div>

            </div>

            <div class="card-body p-4">

                <div class="mb-3">

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
                        value="{{ old('email', $company->email) }}"
                        class="form-control @error('email') is-invalid @enderror"
                    >

                    @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                <div class="mb-3">

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
                        value="{{ old('phone', $company->phone) }}"
                        class="form-control @error('phone') is-invalid @enderror"
                    >

                    @error('phone')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                <div>

                    <label
                        for="website"
                        class="form-label fw-semibold"
                    >
                        {{ __('Website') }}
                    </label>

                    <input
                        type="url"
                        id="website"
                        name="website"
                        value="{{ old('website', $company->website) }}"
                        placeholder="https://example.com"
                        class="form-control @error('website') is-invalid @enderror"
                    >

                    @error('website')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

            </div>

        </div>

    </div>

    <div class="col-12">

        <div class="card fm-card">

            <div class="card-header fm-card-header">

                <div>
                    <h2 class="fm-card-title">
                        {{ __('Address') }}
                    </h2>

                    <p class="fm-card-subtitle">
                        {{ __('Registered or primary business location') }}
                    </p>
                </div>

            </div>

            <div class="card-body p-4">

                <div class="row g-3">

                    <div class="col-12">

                        <label
                            for="address"
                            class="form-label fw-semibold"
                        >
                            {{ __('Address') }}
                        </label>

                        <input
                            type="text"
                            id="address"
                            name="address"
                            value="{{ old('address', $company->address) }}"
                            class="form-control @error('address') is-invalid @enderror"
                        >

                        @error('address')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="col-12 col-md-4">

                        <label
                            for="city"
                            class="form-label fw-semibold"
                        >
                            {{ __('City') }}
                        </label>

                        <input
                            type="text"
                            id="city"
                            name="city"
                            value="{{ old('city', $company->city) }}"
                            class="form-control @error('city') is-invalid @enderror"
                        >

                        @error('city')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="col-12 col-md-3">

                        <label
                            for="province"
                            class="form-label fw-semibold"
                        >
                            {{ __('Province / State') }}
                        </label>

                        <input
                            type="text"
                            id="province"
                            name="province"
                            value="{{ old('province', $company->province) }}"
                            class="form-control @error('province') is-invalid @enderror"
                        >

                        @error('province')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="col-12 col-md-3">

                        <label
                            for="postal_code"
                            class="form-label fw-semibold"
                        >
                            {{ __('Postal code') }}
                        </label>

                        <input
                            type="text"
                            id="postal_code"
                            name="postal_code"
                            value="{{ old('postal_code', $company->postal_code) }}"
                            class="form-control @error('postal_code') is-invalid @enderror"
                        >

                        @error('postal_code')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="col-12 col-md-2">

                        <label
                            for="country_code"
                            class="form-label fw-semibold"
                        >
                            {{ __('Country') }}
                        </label>

                        <input
                            type="text"
                            id="country_code"
                            name="country_code"
                            maxlength="2"
                            value="{{ old('country_code', $company->country_code ?: 'IT') }}"
                            class="form-control text-uppercase @error('country_code') is-invalid @enderror"
                        >

                        @error('country_code')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="col-12">

        <div class="card fm-card">

            <div class="card-header fm-card-header">

                <div>
                    <h2 class="fm-card-title">
                        {{ __('Notes') }}
                    </h2>

                    <p class="fm-card-subtitle">
                        {{ __('Internal information about the company') }}
                    </p>
                </div>

            </div>

            <div class="card-body p-4">

                <textarea
                    id="notes"
                    name="notes"
                    rows="5"
                    class="form-control @error('notes') is-invalid @enderror"
                >{{ old('notes', $company->notes) }}</textarea>

                @error('notes')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

            </div>

        </div>

    </div>

</div>
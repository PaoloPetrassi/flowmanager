@php
    $currentLocale = app()->getLocale();
@endphp

<div
    class="fm-language-switcher"
    role="group"
    aria-label="{{ __('Change language') }}"
>
    <span class="fm-language-icon" aria-hidden="true">
        <i class="bi bi-translate"></i>
    </span>

    @foreach (config('app.supported_locales') as $localeCode => $localeName)
        <form
            method="POST"
            action="{{ route('locale.update', $localeCode) }}"
            class="fm-language-form"
        >
            @csrf

            <button
                type="submit"
                class="fm-language-option {{ $currentLocale === $localeCode ? 'is-active' : '' }}"
                lang="{{ $localeCode }}"
                aria-pressed="{{ $currentLocale === $localeCode ? 'true' : 'false' }}"
                title="{{ $localeName }}"
            >
                {{ strtoupper($localeCode) }}
            </button>
        </form>
    @endforeach
</div>

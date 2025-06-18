@php
    $hasApiKey = !empty($apiKey ?? '');
@endphp

<div class="btn-group" role="group">
    <button type="button" class="btn btn-outline-primary" id="generate-api-key" title="{{ trans('packages/api::api.generate_api_key') }}">
        <x-core::icon name="ti ti-refresh" />
        <span class="d-none d-sm-inline ms-1">{{ trans('packages/api::api.generate_api_key') }}</span>
    </button>

    <x-core::copy
        :copyableState="''"
        :copyableMessage="trans('packages/api::api.api_key_copied')"
        class="btn btn-outline-secondary {{ !$hasApiKey ? 'd-none' : '' }}"
        title="{{ trans('packages/api::api.copy_api_key') }}"
        id="copy-api-key"
        data-clipboard-target="#api-key-input"
    >
        <x-core::icon name="ti ti-clipboard" class="me-0" />
        <span class="d-none d-sm-inline ms-1">{{ trans('packages/api::api.copy_api_key') }}</span>
    </x-core::copy>
</div>

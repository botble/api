<div class="mb-3 api-key-field">
    <label for="api-key-input" class="form-label">
        {{ trans('packages/api::api.api_key') }}
    </label>

    <div class="row g-2">
        <div class="col">
            <input
                type="text"
                class="form-control"
                id="api-key-input"
                name="api_key"
                value="{{ $apiKey }}"
                placeholder="{{ trans('packages/api::api.api_key_placeholder') }}"
                autocomplete="off"
                @if(!empty($apiKey)) readonly @endif
            >
        </div>
        <div class="col-auto">
            @include('packages/api::settings.partials.api-key-actions', ['apiKey' => $apiKey])
        </div>
    </div>
    
    <div class="form-text">
        {{ trans('packages/api::api.api_key_description') }}
    </div>
    
    @if(!empty($apiKey))
        <div class="mt-2">
            <small class="text-success">
                <x-core::icon name="ti ti-shield-check" class="me-1" />
                {!! trans('packages/api::api.api_key_enabled_status') !!}
            </small>
        </div>
    @else
        <div class="mt-2">
            <small class="text-warning">
                <x-core::icon name="ti ti-shield-x" class="me-1" />
                {!! trans('packages/api::api.api_key_disabled_status') !!}
            </small>
        </div>
    @endif
</div>

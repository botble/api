@php
    $docsUrl = url('/docs');
@endphp

<div class="card border-0 bg-white api-documentation-card">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div class="flex-grow-1">
                <h6 class="mb-1">{{ trans('packages/api::api.api_documentation') }}</h6>
                <p class="mb-2 text-muted">{{ trans('packages/api::api.api_documentation_description') }}</p>
                <a href="{{ $docsUrl }}" target="_blank" class="btn btn-sm btn-primary">
                    <x-core::icon name="ti ti-external-link" class="me-1" />
                    {{ trans('packages/api::api.view_documentation') }}
                </a>
            </div>
        </div>
    </div>
</div>

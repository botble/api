@php
    $docsUrl = url('/docs');
    $docsExists = is_dir(public_path('docs'));
@endphp

<div>
    <h6 class="mb-3">{{ trans('packages/api::api.api_documentation') }}</h6>

    @if($docsExists)
        <p class="mb-2 text-muted">{{ trans('packages/api::api.api_documentation_description') }}</p>
        <a href="{{ $docsUrl }}" target="_blank" class="btn btn-sm btn-primary">
            <x-core::icon name="ti ti-external-link" class="me-1" />
            {{ trans('packages/api::api.view_documentation') }}
        </a>
    @else
        <div class="alert alert-info mb-0 d-block">
            <h6 class="alert-heading mb-3">
                <x-core::icon name="ti ti-info-circle" class="me-1" />
                {{ trans('packages/api::api.generate_docs_title') }}
            </h6>
            <p class="mb-3">{{ trans('packages/api::api.generate_docs_description') }}</p>

            <div class="mb-3">
                <strong>{{ trans('packages/api::api.generate_docs_step_1') }}</strong>
                <pre class="mt-2 p-2 bg-dark text-white rounded"><code>composer require knuckleswtf/scribe</code></pre>
            </div>

            <div class="mb-3">
                <strong>{{ trans('packages/api::api.generate_docs_step_2') }}</strong>
                <pre class="mt-2 p-2 bg-dark text-white rounded"><code>php artisan scribe:generate</code></pre>
            </div>

            <div class="mb-0">
                <strong>{{ trans('packages/api::api.generate_docs_step_3') }}</strong>
                <p class="mt-2 mb-0">
                    <a href="{{ $docsUrl }}" target="_blank" class="text-decoration-none">
                        {{ $docsUrl }}
                    </a>
                </p>
            </div>
        </div>
    @endif
</div>

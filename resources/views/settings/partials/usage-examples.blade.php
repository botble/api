@php
    $baseUrl = url('/api/v1');
@endphp

<div class="mt-3">
    <h6>{{ trans('packages/api::api.api_usage_examples') }}</h6>

    <div class="mb-3 api-code-example">
        <label class="form-label">{{ trans('packages/api::api.api_usage_curl_example') }}</label>
        <div class="position-relative">
            <pre class="bg-dark text-light p-3 rounded" style="font-size: 0.875rem;"><code id="curl-example">curl -X GET "{{ $baseUrl }}/pages" \
     -H "Accept: application/json" \
     -H "X-API-KEY: your-api-key-here"</code></pre>
        </div>
    </div>

    <div class="mb-3 api-code-example">
        <label class="form-label">{{ trans('packages/api::api.api_usage_javascript_example') }}</label>
        <div class="position-relative">
            <pre class="bg-dark text-light p-3 rounded" style="font-size: 0.875rem;"><code id="js-example">fetch("{{ $baseUrl }}/pages", {
    method: "GET",
    headers: {
        "Accept": "application/json",
        "X-API-KEY": "your-api-key-here"
    }
})
.then(response => response.json())
.then(data => console.log(data));</code></pre>
        </div>
    </div>
</div>

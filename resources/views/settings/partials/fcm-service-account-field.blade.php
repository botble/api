<div class="mb-3 fcm-service-account-field">
    <label for="fcm-service-account-input" class="form-label">
        {{ trans('packages/api::api.fcm_service_account_path') }}
    </label>

    <div class="row g-2">
        <div class="col">
            <input
                type="text"
                class="form-control"
                id="fcm-service-account-input"
                name="fcm_service_account_path"
                value="{{ $fcmServiceAccountPath }}"
                placeholder="{{ trans('packages/api::api.fcm_service_account_path_placeholder') }}"
                autocomplete="off"
                readonly
            >
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-outline-primary" id="upload-service-account-btn">
                <x-core::icon name="ti ti-upload" />
                {{ trans('packages/api::api.upload_file') }}
            </button>
        </div>
        @if(!empty($fcmServiceAccountPath))
            <div class="col-auto">
                <button type="button" class="btn btn-outline-danger" id="remove-service-account-btn" title="{{ trans('packages/api::api.remove_file') }}">
                    <x-core::icon name="ti ti-trash" />
                </button>
            </div>
        @endif
    </div>

    <div class="form-text">
        {{ trans('packages/api::api.fcm_service_account_upload_description') }}
    </div>

    @if(!empty($fcmServiceAccountPath))
        <div class="mt-2" id="service-account-status">
            <small class="text-success">
                <x-core::icon name="ti ti-file-check" class="me-1" />
                Service account file: <strong>{{ basename($fcmServiceAccountPath) }}</strong>
                <span class="text-muted">({{ trans('packages/api::api.uploaded_at') }}: {{ file_exists(storage_path('app/' . $fcmServiceAccountPath)) ? date('Y-m-d H:i:s', filemtime(storage_path('app/' . $fcmServiceAccountPath))) : 'Unknown' }})</span>
            </small>
        </div>
    @else
        <div class="mt-2" id="service-account-status">
            <small class="text-warning">
                <x-core::icon name="ti ti-file-x" class="me-1" />
                Service account file is <strong>not uploaded</strong>. {{ trans('packages/api::api.upload_json_file') }}
            </small>
        </div>
    @endif

    <!-- Hidden file input for upload -->
    <input type="file" id="service-account-file-input" accept=".json" style="display: none;">

    <!-- Upload progress -->
    <div id="upload-progress" class="mt-2" style="display: none;">
        <div class="progress">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
        </div>
        <small class="text-muted mt-1 d-block">{{ trans('packages/api::api.uploading_file') }}...</small>
    </div>
</div>

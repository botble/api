<div class="mt-4 send-notification-section">
    <h6 class="mb-3">{{ trans('packages/api::api.send_custom_notification') }}</h6>

    <div class="card border-0 bg-white">
        <div class="card-body">
            <div id="send-notification-form">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="notification-title" class="form-label">
                                {{ trans('packages/api::api.notification_title') }}
                                <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                id="notification-title"
                                name="title"
                                placeholder="{{ trans('packages/api::api.notification_title_placeholder') }}"
                                required
                                maxlength="100"
                            >
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="notification-target" class="form-label">
                                {{ trans('packages/api::api.notification_target') }}
                            </label>
                            <select class="form-select" id="notification-target" name="target">
                                <option value="all">{{ trans('packages/api::api.all_devices') }}</option>
                                <option value="android">{{ trans('packages/api::api.android_devices') }}</option>
                                <option value="ios">{{ trans('packages/api::api.ios_devices') }}</option>
                                <option value="customers">{{ trans('packages/api::api.customer_devices') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notification-message" class="form-label">
                        {{ trans('packages/api::api.notification_message') }}
                        <span class="text-danger">*</span>
                    </label>
                    <textarea
                        class="form-control"
                        id="notification-message"
                        name="message"
                        rows="3"
                        placeholder="{{ trans('packages/api::api.notification_message_placeholder') }}"
                        required
                        maxlength="500"
                    ></textarea>
                    <div class="form-text">
                        {{ trans('packages/api::api.notification_message_help') }}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="notification-action-url" class="form-label">
                                {{ trans('packages/api::api.notification_action_url') }}
                            </label>
                            <input
                                type="url"
                                class="form-control"
                                id="notification-action-url"
                                name="action_url"
                                placeholder="{{ trans('packages/api::api.notification_action_url_placeholder') }}"
                            >
                            <div class="form-text">
                                {{ trans('packages/api::api.notification_action_url_help') }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="notification-image-url" class="form-label">
                                {{ trans('packages/api::api.notification_image_url') }}
                            </label>
                            <input
                                type="url"
                                class="form-control"
                                id="notification-image-url"
                                name="image_url"
                                placeholder="{{ trans('packages/api::api.notification_image_url_placeholder') }}"
                            >
                            <div class="form-text">
                                {{ trans('packages/api::api.notification_image_url_help') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        <small id="notification-send-info">
                            <x-core::icon name="ti ti-info-circle" class="me-1" />
                            {{ trans('packages/api::api.notification_send_info') }}
                        </small>
                    </div>
                    <button type="button" class="btn btn-primary" id="send-notification-btn">
                        <x-core::icon name="ti ti-send" class="me-1" />
                        <span>{{ trans('packages/api::api.send_notification') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="notification-result" class="mt-3" style="display: none;"></div>
</div>

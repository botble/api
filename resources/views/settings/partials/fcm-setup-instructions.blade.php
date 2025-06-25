<div class="mt-3">
    <div class="card border-0 bg-white">
        <div class="card-body">
            <h6 class="mb-3">
                <x-core::icon name="ti ti-info-circle" class="me-2" />
                {{ trans('packages/api::api.fcm_setup_title') }}
            </h6>

            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary">{{ trans('packages/api::api.fcm_step_1_title') }}</h6>
                    <ul class="small mb-3">
                        <li>{{ trans('packages/api::api.fcm_step_1_1') }}</li>
                        <li>{{ trans('packages/api::api.fcm_step_1_2') }}</li>
                        <li>{{ trans('packages/api::api.fcm_step_1_3') }}</li>
                    </ul>
                </div>

                <div class="col-md-6">
                    <h6 class="text-primary">{{ trans('packages/api::api.fcm_step_2_title') }}</h6>
                    <ul class="small mb-3">
                        <li>{{ trans('packages/api::api.fcm_step_2_1') }}</li>
                        <li>{{ trans('packages/api::api.fcm_step_2_2') }}</li>
                        <li>{{ trans('packages/api::api.fcm_step_2_3') }}</li>
                        <li>{{ trans('packages/api::api.fcm_step_2_4') }}</li>
                    </ul>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary">{{ trans('packages/api::api.fcm_step_3_title') }}</h6>
                    <ul class="small mb-3">
                        <li>{{ trans('packages/api::api.fcm_step_3_1') }}</li>
                        <li>{{ trans('packages/api::api.fcm_step_3_2') }}</li>
                        <li>{{ trans('packages/api::api.fcm_step_3_3') }}</li>
                    </ul>
                </div>

                <div class="col-md-6">
                    <h6 class="text-primary">{{ trans('packages/api::api.fcm_step_4_title') }}</h6>
                    <ul class="small mb-3">
                        <li>{{ trans('packages/api::api.fcm_step_4_1') }}</li>
                        <li>{{ trans('packages/api::api.fcm_step_4_2') }}</li>
                    </ul>
                </div>
            </div>

            <div class="alert alert-info mb-0">
                <x-core::icon name="ti ti-bulb" class="me-2" />
                <p class="mb-0">
                    <strong>{{ trans('packages/api::api.fcm_security_note_title') }}</strong>
                    {{ trans('packages/api::api.fcm_security_note') }}
                </p>
            </div>
        </div>
    </div>
</div>

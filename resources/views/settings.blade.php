@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <x-core::form
        :url="route('api.settings.update')"
        method="post"
    >
        <x-core-setting::section
            :title="trans('packages/api::api.setting_title')"
            :description="trans('packages/api::api.setting_description')"
        >
            <x-core::form.on-off.checkbox
                name="api_enabled"
                :label="trans('packages/api::api.api_enabled')"
                :checked="ApiHelper::enabled()"
                :wrapper="false"
            />
        </x-core-setting::section>

        <x-core-setting::section.action>
            <x-core::button
                type="submit"
                color="primary"
                icon="ti ti-device-floppy"
            >
                {{ trans('packages/api::api.save_settings') }}
            </x-core::button>
        </x-core-setting::section.action>
    </x-core::form>
@endsection

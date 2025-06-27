@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div
        data-upload-service-account-url="{{ route('api.upload-service-account') }}"
        data-remove-service-account-url="{{ route('api.remove-service-account') }}"
        data-send-notification-url="{{ route('api.send-notification') }}"
        data-device-tokens-stats-url="{{ route('api.device-tokens-stats') }}"
        id="api-settings-container"
    >
        {!! $form->renderForm() !!}
    </div>

    @if (ApiHelper::enabled() && auth()->user()->hasPermission('api.sanctum-token.index'))
        <div class="mt-5">
            <x-core-setting::section :card="false">
                {!! $sanctumTokenTable->renderTable() !!}
            </x-core-setting::section>
        </div>
    @endif
@endsection

@push('footer')
    <script>
        'use strict';

        window.trans = window.trans || {};
        window.trans.api = {!! json_encode($translations, JSON_HEX_APOS) !!};
    </script>
@endpush

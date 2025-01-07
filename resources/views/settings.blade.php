@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    {!! $form->renderForm() !!}

    @if (ApiHelper::enabled() && auth()->user()->hasPermission('api.sanctum-token.index'))
        <div class="mt-5">
            <x-core-setting::section :card="false">
                {!! $sanctumTokenTable->renderTable() !!}
            </x-core-setting::section>
        </div>
    @endif
@endsection

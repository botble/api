<div class="table-wrapper">
    @if(session()->has('plainTextToken'))
        <x-core::alert
            type="success"
            :title="trans('packages/api::sanctum-token.generated_message')"
        >
            <div class="d-flex align-items-center gap-1 mt-2">
                <code>{{ session('plainTextToken') }}</code>

                <a
                    href="javascript:void(0);"
                    data-bb-toggle="clipboard"
                    data-clipboard-action="copy"
                    data-clipboard-text="{{ session('plainTextToken') }}"
                    data-clipboard-message="{{ trans('core/table::table.copied') }}"
                    data-bs-toggle="tooltip"
                    title="{{ trans('core/table::table.copy') }}"
                    class="text-muted text-center text-decoration-none"
                >
                    <span class="sr-only">{{ trans('core/table::table.copy') }}</span>
                    <x-core::icon name="ti ti-clipboard" />
                </a>
            </div>
        </x-core::alert>
    @endif

    <x-core::card>
        <x-core::card.header>
            <div class="w-100 gap-2 d-flex align-items-start justify-content-between flex-wrap">
                <div class="d-flex gap-2 mb-1">
                    @if ($table->hasBulkActions())
                        <x-core::dropdown
                            type="button"
                            :label="trans('core/table::table.bulk_actions')"
                        >
                            @foreach ($table->getBulkActions() as $action)
                                {!! $action !!}
                            @endforeach
                        </x-core::dropdown>
                    @endif

                    @if ($table->hasFilters())
                        <x-core::button
                            type="button"
                            class="btn-show-table-options"
                        >
                            {{ trans('core/table::table.filters') }}
                        </x-core::button>
                    @endif

                    <div class="table-search-input">
                        <label><input type="search" class="form-control input-sm" placeholder="{{ trans('core/table::table.search') }}" style="min-width: 120px"></label>
                    </div>
                </div>

                <div class="d-flex gap-2 mb-1">
                    @foreach($table->getButtons() as $button)
                        @if (Arr::get($button, 'extend') === 'collection')
                            <div class="dropdown">
                                <button class="btn buttons-collection dropdown-toggle {{ $button['className'] }}" data-bs-toggle="dropdown" tabindex="0" aria-controls="{{ $table->getOption('id') }}" type="button" aria-haspopup="dialog" aria-expanded="false">
                                    {!! $button['text'] !!}
                                </button>
                                <div class="dropdown-menu">
                                    @foreach($button['buttons'] as $buttonItem)
                                        <button class="dropdown-item {{ $buttonItem['className'] }}">
                                            {!! $buttonItem['text'] !!}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <button class="btn {{ $button['className'] }}" tabindex="0" aria-controls="{{ $table->getOption('id') }}" type="button" aria-haspopup="dialog" aria-expanded="false">
                                {!! $button['text'] !!}
                            </button>
                        @endif
                    @endforeach

                    @foreach($table->getDefaultButtons() as $defaultButton)
                        @if (is_string($defaultButton))
                            @switch($defaultButton)
                                @case('reload')
                                    <button class="btn item-action" data-bb-toggle="dt-buttons" data-bb-target=".buttons-reload" tabindex="0" aria-controls="{{ $table->getOption('id') }}" type="button" aria-haspopup="dialog" aria-expanded="false">
                                            <span>
                                                <x-core::icon name="ti ti-refresh" /> {{ trans('core/base::tables.reload') }}
                                            </span>
                                    </button>
                                    @break
                                @case('export')
                                    <div class="dropdown">
                                        <button class="btn buttons-collection dropdown-toggle buttons-export" data-bs-toggle="dropdown" tabindex="0" aria-controls="{{ $table->getOption('id') }}" type="button" aria-haspopup="dialog" aria-expanded="false">
                                                <span>
                                                    <x-core::icon name="ti ti-download" /> {{ trans('core/base::tables.export') }}
                                                </span>
                                        </button>
                                        <div class="dropdown-menu">
                                            <button class="dropdown-item" data-bb-toggle="dt-exports" data-bb-target="csv" aria-controls="{{ $table->getOption('id') }}">
                                                    <span>
                                                        <x-core::icon name="ti ti-file-type-csv" /> {{ trans('core/base::tables.csv') }}
                                                    </span>
                                            </button>
                                            <button class="dropdown-item" data-bb-toggle="dt-exports" data-bb-target="excel" aria-controls="{{ $table->getOption('id') }}">
                                                    <span>
                                                        <x-core::icon name="ti ti-file-type-xls" /> {{ trans('core/base::tables.excel') }}
                                                    </span>
                                            </button>
                                        </div>
                                    </div>
                                    @break
                                @case('visibility')
                                    @break
                            @endswitch
                        @else
                            @dd($defaultButton)
                        @endif
                    @endforeach
                </div>
            </div>
        </x-core::card.header>

        <div class="card-table">
            <div class="table-responsive">
                {!! $dataTable->table(compact('id', 'class'), false) !!}
            </div>
        </div>
    </x-core::card>
</div>

@push('footer')
    @include('core/table::modal')

    {!! $dataTable->scripts() !!}
@endpush

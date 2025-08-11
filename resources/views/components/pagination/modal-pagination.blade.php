@props([
    'extremeLinks' => false,
    'paginator',
    'pageOptions' => [],
    'perPage' => 10,
    'previousPageActionName' => 'previousPage',
    'nextPageActionName' => 'nextPage',
    'goToPageActionName' => 'gotoPage',
    'changePageOptionActionName' => 'changePageOption',
])

@php
    use Illuminate\Contracts\Pagination\CursorPaginator;
    use Illuminate\Support\Js;

    $isRtl = __('filament-panels::layout.direction') === 'rtl';
    $isSimple = ! $paginator instanceof \Illuminate\Pagination\LengthAwarePaginator;
    
    $generateActionParameters = fn (array $data) => JS::from($data);
@endphp

<nav
    aria-label="{{ __('filament::components/pagination.label') }}"
    role="navigation"
    {{
        $attributes
            ->whereDoesntStartWith('page:nextBtn')
            ->class([
                'fi-pagination grid grid-cols-[1fr_auto_1fr] items-center gap-x-3',
                'fi-simple' => $isSimple,
            ])
    }}
>
    @if (! $paginator->onFirstPage())
        @php
            if ($paginator instanceof CursorPaginator) {
                $wireClickAction = "mountAction('{$previousPageActionName}', " . $generateActionParameters(['page' => $paginator->previousCursor()->encode(), 'pageName' => $paginator->getCursorName()]) . ")";
            } else {
                $wireClickAction = "mountAction('{$previousPageActionName}', " . $generateActionParameters(['pageName' => $paginator->getPageName()]) . ")";
            }
        @endphp

        <x-filament::button
            color="gray"
            rel="prev"
            :wire:click="$wireClickAction"
            :wire:key="$this->getId() . '.pagination.previous'"
            class="fi-pagination-previous-btn justify-self-start"
        >
            {{ __('filament::components/pagination.actions.previous.label') }}
        </x-filament::button>
    @endif

    @if (! $isSimple)
        <span
            class="fi-pagination-overview text-sm font-medium text-gray-700 dark:text-gray-200"
        >
            {{
                trans_choice(
                    'filament::components/pagination.overview',
                    $paginator->total(),
                    [
                        'first' => \Illuminate\Support\Number::format($paginator->firstItem() ?? 0),
                        'last' => \Illuminate\Support\Number::format($paginator->lastItem() ?? 0),
                        'total' => \Illuminate\Support\Number::format($paginator->total()),
                    ],
                )
            }}
        </span>
    @endif

    @if (count($pageOptions) > 1)
        <div class="col-start-2 justify-self-center" x-data="{ 
            option: @js($perPage),
            init() {
                $watch('option', value => {
                    if (value) {
                        $wire.mountAction('{{$changePageOptionActionName}}', { value: value });
                    }
                });
            },
        }">
            <label class="fi-pagination-records-per-page-select fi-compact">
                <x-filament::input.wrapper>
                    <x-filament::input.select
                        x-model="option"
                    >
                        @foreach ($pageOptions as $option)
                            <option value="{{ $option }}">
                                {{ $option === 'all' ? __('filament::components/pagination.fields.records_per_page.options.all') : $option }}
                            </option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>

                <span class="sr-only">
                    {{ __('filament::components/pagination.fields.records_per_page.label') }}
                </span>
            </label>

            <label class="fi-pagination-records-per-page-select">
                <x-filament::input.wrapper
                    :prefix="__('filament::components/pagination.fields.records_per_page.label')"
                >
                    <x-filament::input.select
                        x-model="option"
                    >
                        @foreach ($pageOptions as $option)
                            <option value="{{ $option }}">
                                {{ $option === 'all' ? __('filament::components/pagination.fields.records_per_page.options.all') : $option }}
                            </option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </label>
        </div>
    @endif

    @if ($paginator->hasMorePages())
        @php
            if ($paginator instanceof CursorPaginator) {
                $wireClickAction = "mountAction('{$goToPageActionName}', " . $generateActionParameters(['page' => $paginator->nextCursor()->encode(), 'pageName' => $paginator->getCursorName()]) . ")";
            } else {
                $wireClickAction = "mountAction('{$nextPageActionName}', " . $generateActionParameters(['pageName' => $paginator->getPageName()]) . ")";
            }
        @endphp

        <x-filament::button
            color="gray"
            rel="next"
            :wire:click="$wireClickAction"
            :wire:key="$this->getId() . '.pagination.next'"
            class="fi-pagination-next-btn col-start-3 justify-self-end"
        >
            {{ __('filament::components/pagination.actions.next.label') }}
        </x-filament::button>
    @endif

    @if ((! $isSimple) && $paginator->hasPages())
        <ol
            class="fi-pagination-items justify-self-end rounded-lg bg-white shadow-xs ring-1 ring-gray-950/10 dark:bg-white/5 dark:ring-white/20"
        >
            @if (! $paginator->onFirstPage())
                @if ($extremeLinks)
                    <x-filament::pagination.item
                        :aria-label="__('filament::components/pagination.actions.first.label')"
                        :icon="$isRtl ? 'heroicon-m-chevron-double-right' : 'heroicon-m-chevron-double-left'"
                        :icon-alias="$isRtl ? 'pagination.first-button.rtl' : 'pagination.first-button'"
                        rel="first"
                        :wire:click="'mountAction(\'' . $goToPageActionName . '\', ' . $generateActionParameters(['page' => 1,'pageName' => $paginator->getPageName()]) . ')'"
                        :wire:key="$this->getId() . '.pagination.first'"
                    />
                @endif

                <x-filament::pagination.item
                    :aria-label="__('filament::components/pagination.actions.previous.label')"
                    :icon="$isRtl ? 'heroicon-m-chevron-right' : 'heroicon-m-chevron-left'"
                    {{-- @deprecated Use `pagination.previous-button.rtl` instead of `pagination.previous-button` for RTL. --}}
                    :icon-alias="$isRtl ? ['pagination.previous-button.rtl', 'pagination.previous-button'] : 'pagination.previous-button'"
                    rel="prev"
                    :wire:click="'mountAction(\'' . $previousPageActionName . '\', ' . $generateActionParameters(['pageName' => $paginator->getPageName()]) . ')'"
                    :wire:key="$this->getId() . '.pagination.previous'"
                />
            @endif

            @foreach ($paginator->render()->offsetGet('elements') as $element)
                @if (is_string($element))
                    <x-filament::pagination.item disabled :label="$element" />
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <x-filament::pagination.item
                            :active="$page === $paginator->currentPage()"
                            :aria-label="trans_choice('filament::components/pagination.actions.go_to_page.label', $page, ['page' => $page])"
                            :label="$page"
                            :wire:click="'mountAction(\'' . $goToPageActionName . '\', ' . $generateActionParameters(['page' => $page,'pageName' => $paginator->getPageName()]) . ')'"
                            :wire:key="$this->getId() . '.pagination.' . $paginator->getPageName() . '.' . $page"
                        />
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <x-filament::pagination.item
                    :aria-label="__('filament::components/pagination.actions.next.label')"
                    :icon="$isRtl ? 'heroicon-m-chevron-left' : 'heroicon-m-chevron-right'"
                    :icon-alias="$isRtl ? ['pagination.next-button.rtl', 'pagination.next-button'] : 'pagination.next-button'"
                    rel="next"
                    :wire:click="'mountAction(\'' . $nextPageActionName . '\', ' . $generateActionParameters(['pageName' => $paginator->getPageName()]) . ')'"
                    :wire:key="$this->getId() . '.pagination.next'"
                />

                @if ($extremeLinks)
                    <x-filament::pagination.item
                        :aria-label="__('filament::components/pagination.actions.last.label')"
                        :icon="$isRtl ? 'heroicon-m-chevron-double-left' : 'heroicon-m-chevron-double-right'"
                        :icon-alias="$isRtl ? 'pagination.last-button.rtl' : 'pagination.last-button'"
                        rel="last"
                        :wire:click="'mountAction(\'' . $goToPageActionName . '\', ' . $generateActionParameters(['page' => $paginator->lastPage(),'pageName' => $paginator->getPageName()]) . ')'"
                        :wire:key="$this->getId() . '.pagination.last'"
                    />
                @endif
            @endif
        </ol>
    @endif
</nav>
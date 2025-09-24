<div class="content-tree-node flex flex-col space-y-2">
    @if (! $isDisabled)
        <x-filament::input.wrapper
            prefix-icon="heroicon-m-magnifying-glass"
            prefix-icon-alias="panels::global-search.field"
            inline-prefix
            class="search-container mb-4"
        >
            <x-filament::input
                autocomplete="off"
                inline-prefix
                maxlength="1000"
                type="search"
                wire:model.live="search"
                :placeholder="@trans('inspirecms::inspirecms.search.placeholder')"
                class="search-input" 
            />
        </x-filament::input.wrapper>

        <div wire:loading wire:target="search"> 
            {{ __('inspirecms::inspirecms.search.message') }}
        </div>
    @endif

    <div>
        {{-- Display total selected count --}}
        @if($enableSelection && count($selectedNodes) > 0)
            <div class="mb-3 px-3 py-2 bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-700 rounded-lg">
                <p class="text-sm text-primary-700 dark:text-primary-300">
                    <x-filament::icon
                        icon="heroicon-s-check-circle"
                        class="h-4 w-4 inline mr-1"
                    />
                    @php
                        $selectedCount = count($selectedNodes);
                    @endphp
                    {{ $selectedCount }} {{ $selectedCount === 1 ? 'item' : 'items' }} selected
                </p>
            </div>
        @endif

        @if ($this->isFilteringBySearch())
            <div class="space-y-3 pb-4">
                @forelse ($searchRecords ?? [] as $item)
                    <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-150 cursor-pointer"
                            @if($enableSelection)
                            wire:click="toggleNodeSelection('{{ $item->getKey() }}')"
                            @class([
                                'ring-2 ring-primary-500 bg-primary-50 dark:bg-primary-900/20' => $this->isNodeSelected($item->getKey()),
                            ])
                            @endif
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                {{-- Title --}}
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    @if($item->title)
                                        {{ $item->title }}
                                    @else
                                        <span class="text-gray-500 dark:text-gray-400 italic">Untitled</span>
                                    @endif
                                </h3>
                                
                                {{-- Slug --}}
                                @if($item->slug)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        <span class="font-mono">/{{ $item->slug }}</span>
                                    </p>
                                @endif
                                
                                {{-- Excerpt --}}
                                {{-- @if($item->excerpt)
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-2 line-clamp-2">
                                        {{ $item->excerpt }}
                                    </p>
                                @endif --}}
                                
                                {{-- Meta information --}}
                                <div class="flex items-center gap-4 mt-3 text-xs text-gray-500 dark:text-gray-400">
                                    @if($item->documentType)
                                        {{-- <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-700">
                                            {{ $item->documentType->name ?? $item->documentType->slug }}
                                        </span> --}}
                                        <x-filament::badge
                                            :icon="$item->documentType->icon"
                                            icon-position="after"
                                            color="gray"
                                        >
                                            {{ $item->documentType->name ?? $item->documentType->slug }}
                                        </x-filament::badge>
                                    @endif
                                    
                                    @if($item->created_at)
                                        <span>
                                            Created: {{ $item->created_at->format('M j, Y') }}
                                        </span>
                                    @endif
                                    
                                    @if($item->updated_at && $item->updated_at != $item->created_at)
                                        <span>
                                            Updated: {{ $item->updated_at->format('M j, Y') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            {{-- Selection indicator --}}
                            @if($enableSelection)
                                <div class="ml-3 flex-shrink-0">
                                    @if($this->isNodeSelected($item->getKey()))
                                        <x-filament::icon
                                            icon="heroicon-s-check-circle"
                                            class="h-5 w-5 text-primary-600 dark:text-primary-400"
                                        />
                                    @else
                                        <div class="h-5 w-5 border-2 border-gray-300 dark:border-gray-600 rounded-full"></div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <p class="text-sm text-gray-500 dark:text-gray-400">No results found.</p>
                    </div>
                @endforelse
            </div>
            
            {{-- Pagination --}}
            <x-filament::pagination 
                :paginator="$searchRecords"
                :page-options="$pageOptions"
                current-page-option-property="paginationPageSize"
                extreme-links
            />
        @elseif (count($nodes) > 0)
            <x-inspirecms-support::tree-node.service-side-tree
                :nodes="$nodes"
                :livewire="$this"
                :hasNodeActions="$showNodeActions"
                :toolbarActions="$toolbarActions"
                :navigationHeaderActions="$navigationHeaderActions"
                :showNavigationHeader="$showNavigationHeader"
                :enableSelection="$enableSelection"
                :multipleSelection="$multipleSelection"
                :enableNodeUrls="$enableNodeUrls"
                :maxSelections="$maxSelections"
                :homeButtonText="$homeButtonText"
                :indexUrl="$indexUrl"
            />
        @else
            <p class="text-gray-500 dark:text-gray-400">
                {{ __('inspirecms::inspirecms.search.no_results') }}
            </p>
        @endif
    </div>

</div>
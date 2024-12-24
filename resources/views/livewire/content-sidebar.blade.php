@php
    $items = $this->getGroupedNodeItems();
    $selectedKey = $this->selectedModelItemKey;
    $translatable ??= false;
    $expandedItemsStateKey ??= null;
    $translatableLocale ??= null;
    $isExpandedSidebar ??= false;
    $sidebarExpanded = true;
@endphp

<div class="model-explorer relative block flex-none bg-white dark:bg-gray-900 shadow-lg  px-2" x-data="{
    sidebarExpanded: @js($sidebarExpanded),
}">
    <div class="top-0 z-20 -ml-0.5 h-screen overflow-y-auto overflow-x-hidden pb-16">
        <div x-bind:class="{
            '!w-12': !sidebarExpanded,
        }" @class([
            'pt-2 pb-1',
            'w-64' => $sidebarExpanded,
        ])>
            <div class="flex items-center justify-start">
                <button @class([
                    'fi-icon-btn relative flex items-center justify-center rounded-lg outline-none transition duration-75 focus-visible:ring-2',
                    'h-10 w-10',
                    'text-gray-400 hover:text-gray-500 focus-visible:ring-primary-600 dark:text-gray-300 dark:hover:text-gray-100 dark:focus-visible:ring-primary-500',
                ])
                    x-on:click="sidebarExpanded = !sidebarExpanded" 
                    title="Expand sidebar"
                >
                    <span class="sr-only">Expand sidebar</span>
                    <svg class="w-5 h-5" x-show="sidebarExpanded" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M21.97 15V9C21.97 4 19.97 2 14.97 2H8.96997C3.96997 2 1.96997 4 1.96997 9V15C1.96997 20 3.96997 22 8.96997 22H14.97C19.97 22 21.97 20 21.97 15Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M7.96997 2V22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M14.97 9.43994L12.41 11.9999L14.97 14.5599" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
                    <svg class="w-5 h-5" x-show="!sidebarExpanded" style="display: none;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M21.97 15V9C21.97 4 19.97 2 14.97 2H8.96997C3.96997 2 1.96997 4 1.96997 9V15C1.96997 20 3.96997 22 8.96997 22H14.97C19.97 22 21.97 20 21.97 15Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M14.97 2V22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M7.96997 9.43994L10.53 11.9999L7.96997 14.5599" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
                </button>

            </div>
        </div>

        <div class="navigation-custom-scrollbar" x-show="sidebarExpanded" @style([
            'display: block' => $sidebarExpanded,
        ])>
            <div class="px-1 pb-2">
                {{ $this->localeSwitcher }}
            </div>
            <nav class="text-base lg:text-sm w-64 lg:block" x-data="{
                expandedItems: @if (filled($expandedItemsStateKey)) $wire.entangle('{{ $expandedItemsStateKey }}') @else [] @endif,
                async toggleItem(key, currDepth) {
                    if (this.expandedItems.includes(key)) {
                        this.expandedItems = this.expandedItems.filter(item => item !== key);
                    } else {
                        this.expandedItems.push(key);
                    }

                    await this.fetchNodes(key, currDepth + 1);
                },
                selectItem(key) {            
                    this.isExpandedSidebar = false
                    Livewire.dispatch('selectItem', [key])
                },
                isExpanded(key) {
                    return this.expandedItems.includes(key);
                },
                async fetchNodes(key, depth) {
                    Livewire.dispatch('getNodes', [key, depth])
                    while (this.fetching) {
                        await new Promise(resolve => setTimeout(resolve, 100));
                    }
                },
                init() {
                    //
                }
            }">
                <ul class="flex flex-col gap-y-1" role="list" aria-expanded="{{ $isExpandedSidebar }}">
                    @foreach ($items as $item)
                        <x-inspirecms-support::model-explorer.item  
                            :item="$item" 
                            :selected-key="$selectedKey"
                            :model-explorer="$modelExplorer"
                            :translatable="$translatable"
                            :translatable-locale="$translatableLocale"
                            :spa-mode="$isSpaMode ?? false"
                        />
                    @endforeach
                </ul>
            </nav>
        </div>
    </div>
    <x-inspirecms-support::tree-node.actions.modals />
</div>
@php
    $items = $this->getGroupedNodeItems();
    $translatable ??= false;
    $translatableLocale ??= null;
    $isExpandedSidebar ??= false;
    $sidebarExpanded = true;
@endphp

<div x-data="{
        sidebarExpanded: $store.contentSidebar.isOpen,
    }"
    class="content-sidebar"
>
    <div x-show="sidebarExpanded" x-cloak
        x-transition:enter="transition-opacity ease-int duration-300" 
        x-transition:enter-start="opacity-0" 
        x-transition:enter-end="opacity-100" 
        x-transition:leave="transition-opacity ease-out duration-300" 
        x-transition:leave-start="opacity-100" 
        x-transition:leave-end="opacity-0"
        class="relative z-30 lg:hidden" 
        role="dialog" 
        aria-modal="true"
    >
        <div class="fixed inset-0 bg-gray-900/80" aria-hidden="true" :class="{'translate-x-0': sidebarExpanded, '-translate-x-full': !sidebarExpanded}"></div>
        <div class="fixed inset-0 flex">
            <div class="relative mr-16 flex w-full max-w-xs flex-1 top-0 content-sidebar_mobile">
                <div class="absolute left-full flex w-16 justify-center pt-5">
                    <button type="button" class="-m-2.5 p-2.5" @click="sidebarExpanded = false">
                    <span class="sr-only">Close sidebar</span>
                    <svg class="size-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                    </button>
                </div>
                <!-- Sidebar component, swap this element with another sidebar if you like -->
                <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-white py-2 dark:bg-gray-900" @click.outside="sidebarExpanded = false">
                    <div class="px-1 pb-2">
                        {{ $this->localeSwitcher }}
                    </div>
                    
                    <x-inspirecms-support::tree-node.model-explorer
                        :livewire-key="$this->getId()"
                        :livewire-name="$this->getName()"
                        :items="$items" 
                        :model-explorer="$modelExplorer"
                        :translatable="$translatable"
                        :translatable-locale="$translatableLocale"
                        :spa-mode="$isSpaMode ?? false"
                    />
                </div>
            </div>
        </div>
    </div>
    <div class="hidden lg:fixed lg:top-16 lg:bottom-0 lg:flex lg:w-72 lg:flex-col content-sidebar_desktop">
        <div class="flex grow flex-col gap-y-5 overflow-y-auto border-r border-gray-200 bg-white py-2 dark:bg-gray-900 dark:border-gray-700">
            <div class="px-1 pb-2">
                {{ $this->localeSwitcher }}
            </div>
            
            <x-inspirecms-support::tree-node.model-explorer
                class="px-1"
                :livewire-key="$this->getId()"
                :livewire-name="$this->getName()"
                :items="$items" 
                :model-explorer="$modelExplorer"
                :translatable="$translatable"
                :translatable-locale="$translatableLocale"
                :spa-mode="$isSpaMode ?? false"
            />
        </div>
    </div>

    <!-- Static sidebar for desktop -->
    <div x-show="!sidebarExpanded" x-cloak
        x-transition:enter="transition-opacity ease-linear duration-300" 
        x-transition:enter-start="opacity-0" 
        x-transition:enter-end="opacity-100" 
        x-transition:leave="transition-opacity ease-linear duration-300" 
        x-transition:leave-start="opacity-100" 
        x-transition:leave-end="opacity-0"
        class="fixed top-16 right-0 z-10 px-2 py-2 lg:hidden icon-btn">
        <button type="button" 
            class="p-4 rounded-full shadow-md bg-white ring-1 ring-gray-300 hover:bg-gray-100 dark:bg-gray-600 dark:ring-gray-400/20 dark:hover:text-gray-400 dark:ring-gray-400/20" 
            @click="sidebarExpanded = !sidebarExpanded"
        >
            <span class="sr-only">Open sidebar</span>
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M21.97 15V9C21.97 4 19.97 2 14.97 2H8.96997C3.96997 2 1.96997 4 1.96997 9V15C1.96997 20 3.96997 22 8.96997 22H14.97C19.97 22 21.97 20 21.97 15Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M14.97 2V22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M7.96997 9.43994L10.53 11.9999L7.96997 14.5599" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
        </button>
    </div>

    <x-filament-actions::modals />
    <x-inspirecms-support::tree-node.actions.modals />
</div>
@php
    $isExpandedSidebar ??= false;
    $sidebarExpanded = true;

    $toolbarActions ??= [];
    $navigationHeaderActions ??= [];
@endphp

<div 
    class="content-sidebar"
>
    <!-- Static sidebar for desktop -->
    <x-filament::modal
        id="content-sidebar-modal"
        class="content-sidebar-modal"
        slide-over
        width="5xl"
        :autofocus="false"
        alignment="center"
        :close-button="true"
        display-classes="block"
    >
        <x-slot name="trigger">
            <div class="fixed top-16 right-0 z-10 px-2 py-2 lg:hidden">
                <x-filament::button 
                    class="p-4 rounded-full shadow-md"
                    color="gray"
                    title="Open sidebar"
                >
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M21.97 15V9C21.97 4 19.97 2 14.97 2H8.96997C3.96997 2 1.96997 4 1.96997 9V15C1.96997 20 3.96997 22 8.96997 22H14.97C19.97 22 21.97 20 21.97 15Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M14.97 2V22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M7.96997 9.43994L10.53 11.9999L7.96997 14.5599" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
                </x-filament::button>
            </div>
        </x-slot>
        <x-slot name="header">
            <div class="tree-toolbar">
                @foreach($toolbarActions as $action)
                    {{$action}}
                @endforeach
            </div>
        </x-slot>
        <x-inspirecms-support::tree-node.service-side-tree
            :nodes="$nodes"
            :livewire="$this"
            :hasNodeActions="$showNodeActions"
            {{-- :toolbarActions="$toolbarActions" --}}
            :navigationHeaderActions="$navigationHeaderActions"
            :showNavigationHeader="$showNavigationHeader"
            :enableSelection="$enableSelection"
            :multipleSelection="$multipleSelection"
            :enableNodeUrls="$enableNodeUrls"
            :maxSelections="$maxSelections"
            :homeButtonText="$homeButtonText"
            :indexUrl="$indexUrl"
        />
    </x-filament::modal>

    <!-- Static sidebar for desktop -->
    <div class="hidden lg:fixed lg:top-y-16 lg:left-0 lg:w-72 lg:flex lg:flex-col content-sidebar_desktop z-40 h-svh">
        <div class="flex grow flex-col gap-y-5 overflow-y-auto overflow-x-visible border-e border-gray-200 bg-white py-2 dark:bg-gray-900 dark:border-gray-700">
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

        </div>
    </div>
    
    <x-filament-actions::modals />
    
</div>
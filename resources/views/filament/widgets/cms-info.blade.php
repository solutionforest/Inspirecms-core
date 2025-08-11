<x-filament-widgets::widget>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 pb-4">
        <a href="{{ $this->getDocumentUrl() }}" class="flex items-center p-6 bg-white rounded-lg shadow-xs dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
            <div class="mr-4">
                <x-filament::icon icon="heroicon-o-book-open" class="w-8 h-8 text-primary-500" />
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-medium">Documentation</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                    Access our comprehensive guides and documentation
                </p>
            </div>
            <x-filament::icon icon="heroicon-o-chevron-right" class="w-5 h-5 text-gray-400" />
        </a>

        <a href="{{ $this->getNewsUrl() }}" class="flex items-center p-6 bg-white rounded-lg shadow-xs dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
            <div class="mr-4">
                <x-filament::icon icon="heroicon-o-newspaper" class="w-8 h-8 text-primary-500" />
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-medium">News</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                    Stay updated with latest features and announcements
                </p>
            </div>
            <x-filament::icon icon="heroicon-o-chevron-right" class="w-5 h-5 text-gray-400" />
        </a>

    </div>

</x-filament-widgets::widget>
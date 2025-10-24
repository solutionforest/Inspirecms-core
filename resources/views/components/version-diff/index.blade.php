@props(['items' => [], 'heading' => null, 'itemsCollapsible' => false, 'defaultCollapsedLevel' => 1])
<div {{ $attributes->class([
    'version-diff',
    'bg-white dark:bg-gray-900',
    'border border-gray-200 dark:border-gray-700',
    'rounded-xl shadow-sm',
    'overflow-hidden',
    'divide-y divide-gray-200 dark:divide-gray-700'
]) }} role="region" aria-label="Version differences" @if($itemsCollapsible) x-data="versionDiffController()" @endif>
    @if (isset($heading))
        <div class="bg-gray-50 dark:bg-gray-800/50 px-4 py-3 sm:px-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ $heading }}
                </h3>
                
                @if ($itemsCollapsible)
                    <div class="flex items-center gap-1">
                        <button 
                            type="button"
                            x-on:click="expandAll()"
                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors"
                            title="Expand All"
                        >
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Expand All
                        </button>
                        <button 
                            type="button"
                            x-on:click="collapseAll()"
                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors"
                            title="Collapse All"
                        >
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6"/>
                            </svg>
                            Collapse All
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif
    
    <div class="version-diff-content px-3 py-2 sm:px-4 sm:py-3">
        @if (isset($items) && is_array($items) && count($items) > 0)
            <x-inspirecms::version-diff.items 
                :items="$items" 
                :level="0"
                :itemsCollapsible="$itemsCollapsible"
                :defaultCollapsedLevel="$defaultCollapsedLevel"/>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="mt-3 text-gray-500 dark:text-gray-400 text-sm font-medium">
                    No differences to display
                </p>
                <p class="mt-1 text-gray-400 dark:text-gray-500 text-xs">
                    The content versions appear to be identical
                </p>
            </div>
        @endif
    </div>
</div>
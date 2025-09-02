@props(['items' => [], 'heading' => null])
<div {{ $attributes->class(['version-diff bg-white dark:bg-gray-900 dark:divide-white/10 divide-y overflow-hidden ring-1 ring-gray-950/5 dark:ring-white/10 rounded-xl shadow-xs']) }}>
    @if (isset($heading))
        <div class="flex items-center gap-4 gap-x-6 bg-gray-50 px-4 py-2 dark:bg-white/5 sm:px-6">
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
                {{ $heading }}
            </h3>
        </div>
    @endif
    @if (isset($items) && is_array($items) && count($items) > 0)
        <x-inspirecms::version-diff.items :items="$items"/>
    @else
        <p class="text-gray-500 text-lg px-3 py-4">{{ __('inspirecms::resources/content-version.content_history_detail.empty_state') }}</p>
    @endif
</div>
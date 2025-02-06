@props([
    'label',
    'message',
    'plaintext',
])
<button type="button"
    class="fi-icon-btn relative flex items-center justify-center rounded-lg outline-none  transition duration-75 focus-visible:ring-2 h-9 w-9 text-gray-400 hover:text-gray-500 focus-visible:ring-primary-600 dark:text-gray-500 dark:hover:text-gray-400 dark:focus-visible:ring-primary-500 -m-2"
    title="{{ $label }}"
    x-on:click="
        window.navigator.clipboard.writeText(@js($plaintext));
            $tooltip('{{ $message }}', {
            theme: $store.theme,
            timeout: 2000,
        })
    "
>
    <span class="sr-only">{{ $label }}</span>
    <x-filament::icon
        icon="heroicon-m-clipboard"
        :label="$label"
        class="h-4 w-4"
    />
</button>
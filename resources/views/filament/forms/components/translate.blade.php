@php
    $components = $getChildComponentContainers();
    $childComponentsStatePath = collect($components)
            ->flatMap(fn ($component) => $component->getFlatComponents())
            ->map(fn ($component) => $component->getStatePath())
            ->all();

    $childComponentHasErrors = collect($childComponentsStatePath)
        ->filter(fn ($statePath) => $errors->has($statePath))
        ->isNotEmpty();
@endphp
<div @class([
    'ring-1 ring-danger-600/50 dark:ring-danger-500/50 rounded-md p-2 shadow-md' => $childComponentHasErrors,
])>
    @if ($childComponentHasErrors)
    <div class="flex justify-end">
        <x-filament-forms::field-wrapper.error-message>
            {{ trans('inspirecms::inspirecms.validation.translatable_child_field_has_error') }}
        </x-filament-forms::field-wrapper.error-message>
    </div>
    @endif

    @foreach ($components as $item)
        {{$item}}
    @endforeach

</div>
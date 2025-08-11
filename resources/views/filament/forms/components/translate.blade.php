@php
    $components = $getChildComponentContainers();
    $childComponentsStatePath = collect($components)
            ->flatMap(fn ($component) => $component->getFlatComponents())
            ->map(fn ($component) => $component->getStatePath())
            ->all();

    $allErrorKeys = collect($errors->getBags())->flatMap(fn ($item) => $item->keys());

    $childComponentHasErrors = collect($childComponentsStatePath)
        ->where(function ($statePath) use ($allErrorKeys) {

            $relativePath = str($statePath)->beforeLast(".")->finish(".")->toString();

            return collect($allErrorKeys)
                ->filter(fn ($key) => str($key)->startsWith($relativePath))
                ->isNotEmpty();
        })
        ->isNotEmpty();
@endphp
<div @class([
    'ring-1 ring-danger-600/50 dark:ring-danger-500/50 rounded-md p-2 shadow-md' => $childComponentHasErrors,
])>
    @if ($childComponentHasErrors)
        <p
            data-validation-error
            class="fi-fo-field-wrp-error-message"
        >
            {{ trans('inspirecms::inspirecms.validation.translatable_child_field_has_error') }}
        </p>
    @endif

    @foreach ($components as $item)
        {{$item}}
    @endforeach

</div>
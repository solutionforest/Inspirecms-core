@php
    use Illuminate\Support\Arr;
    use SolutionForest\InspireCms\Dtos\PropertyTypeDto;
    use SolutionForest\InspireCms\Helpers\FieldTypeHelper;

    $propertyTypes = collect($getState())->map(function ($arr) {
        $data = $arr['dtoData'] ?? [];
        $data['config'] = FieldTypeHelper::getFieldTypeConfig($arr['fieldType'], $data['config'] ?? []);
        return PropertyTypeDto::fromArray($data);
    });
    //todo: add translation
@endphp

<x-filament::section
    collapsible
    compact
>
    <x-slot name="heading">
        {{ trans('inspirecms::resources/template.property_type_instructions.label') }}
    </x-slot>
    <div class="flex flex-col space-y-4 w-full">
        @foreach ($propertyTypes as $propertyType)
            @php
                $fieldType = $propertyType->config;

                $fieldTypeConfig = $fieldType ? Arr::first($fieldType->getConfigNames()) : [];
                $fieldTypeName = $fieldTypeConfig['name'] ?? null;
                $icon = $fieldTypeConfig['icon'] ?? null;

                $translatable = $fieldType?->isTranslatable() ?? false;
            @endphp
            <x-filament::section 
                collapsible
                collapsed
                :icon="$icon"
                icon-size="md"
                compact
                x-tooltip="{
                    content: 'Field type: {{ $fieldTypeName }}',
                    placement: 'right',
                    theme: $store.theme,
                }"  
            >
                <x-slot name="heading">
                    Field: {{ $propertyType->key }}
                </x-slot>
                <x-slot name="description">
                    Group: {{ $propertyType->group }}
                </x-slot>
                @if ($translatable)
                    <x-slot name="headerEnd">
                        <x-filament::icon-button
                            icon="heroicon-o-language"
                            color="gray"
                            tooltip="Translatable"
                        />
                    </x-slot>
                @endif
            
                <p class="font-mono text-xs">
                    $content->getPropertyGroup('{{ $propertyType->group }}')?->('{{ $propertyType->key }}')?->getValue(@if ($translatable)'$locale'@endif);
                </p>
        </x-filament::section>
    @endforeach
    </div>
</x-filament::section>
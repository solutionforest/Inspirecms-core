@php
    use Illuminate\Support\Arr;
    use SolutionForest\InspireCms\Dtos\PropertyTypeDto;
    use SolutionForest\InspireCms\Helpers\FieldTypeHelper;

    $groupedPropertyTypes = collect($getState())->map(function ($arr) {
        $data = $arr['dtoData'] ?? [];
        $data['config'] = FieldTypeHelper::getFieldTypeConfig($arr['fieldType'], $data['config'] ?? []);
        return PropertyTypeDto::fromArray($data);
    })->groupBy('group')->sortKeys();
    //todo: add translation
@endphp

<div>
    <x-filament-forms::field-wrapper.label class="pb-2">
        {{ trans('inspirecms::resources/template.property_type_instructions.label') }}
    </x-filament-forms::field-wrapper.label>
    <div class="flex flex-col space-y-4 w-full">
        @foreach ($groupedPropertyTypes as $group => $propertyTypes)
        
            <x-filament::section compact>
                <x-slot name="heading">
                    {{ $group }}
                </x-slot>
                <x-slot name="headerEnd">
                    Group
                </x-slot>

                <div class="flex flex-col gap-y-2">
                    @foreach ($propertyTypes as $propertyType)
                        @php
                            $fieldType = $propertyType->config;

                            $fieldTypeConfig = $fieldType ? Arr::first($fieldType->getConfigNames()) : [];
                            $fieldTypeName = $fieldTypeConfig['name'] ?? null;
                            $icon = $fieldTypeConfig['icon'] ?? null;

                            $translatable = $fieldType?->isTranslatable() ?? false;

                            $plaintext = '$content->getPropertyGroup(\'' . $propertyType->group . '\')?->getPropertyData(\'' . $propertyType->key . '\')?->getValue(' . ($translatable ? html_entity_decode('$locale') : '') . ');';
                        @endphp
                        <x-filament::section 
                            collapsible
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
                            @if ($translatable)
                                <x-slot name="headerEnd">
                                    <x-filament::icon-button
                                        icon="heroicon-o-language"
                                        color="gray"
                                        tooltip="Translatable"
                                    />
                                </x-slot>
                            @endif
                        
                            
                            <div class="flex gap-x-2 justify-between">
                                <p class="font-mono text-xs">
                                   {{ $plaintext }}
                                </p>

                                <button type="button"
                                    class="fi-icon-btn relative flex items-center justify-center rounded-lg outline-none  transition duration-75 focus-visible:ring-2 h-9 w-9 text-gray-400 hover:text-gray-500 focus-visible:ring-primary-600 dark:text-gray-500 dark:hover:text-gray-400 dark:focus-visible:ring-primary-500 -m-2"
                                    title="{{ $copyButtonLabel }}"
                                    x-on:click="
                                        window.navigator.clipboard.writeText(@js($plaintext));
                                            $tooltip('{{ $copiedMessage }}', {
                                            theme: $store.theme,
                                            timeout: 2000,
                                        })
                                    "
                                >
                                    <span class="sr-only">{{ $copyButtonLabel }}</span>
                                    <x-filament::icon
                                        icon="heroicon-m-clipboard"
                                        :label="$copyButtonLabel"
                                        class="h-4 w-4"
                                    />
                                </button>
                            </div>
                        </x-filament::section>
                    @endforeach
                </div>

            </x-filament::section>
        @endforeach
    </div>
</div>
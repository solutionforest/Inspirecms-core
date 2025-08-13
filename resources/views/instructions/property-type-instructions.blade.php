@use('SolutionForest\InspireCms\Helpers\PropertyTypeHelper')
@php
    use Illuminate\Support\Arr;
    use SolutionForest\InspireCms\Dtos\PropertyTypeDto;
    use SolutionForest\InspireCms\Helpers\FieldTypeHelper;
    use SolutionForest\InspireCms\Helpers\TemplateHelper;

    $groupedPropertyTypes = collect($getState())->map(function ($arr) {
        $data = $arr['dtoData'] ?? [];
        $data['config'] = FieldTypeHelper::getFieldTypeConfig($arr['fieldType'], $data['config'] ?? []);
        return PropertyTypeDto::fromArray($data);
    })->groupBy('group')->sortKeys();

    $getPlainTextAndSampleCodeForField = function ($fieldType, $group, $field) {

        $translatable = $fieldType?->isTranslatable() ?? false;

        $valueType = PropertyTypeHelper::getFieldDisplayValueType($fieldType);

        $propertyVarName = TemplateHelper::generatePropertyVarName($group, $field);

        if ($valueType == 'boolean') {
            return [
                "
@if (\$content?->getPropertyGroup('{$group}')?->getPropertyData('{$field}')?->getValue() ?? false)
@endif"
            ];
        }

        if ($valueType != 'array' || $translatable) {
            $result[] = "
@property('{$group}', '{$field}')";
        } else {
            $result[] = "
@propertyArray('{$group}', '{$field}')
@foreach (\${$propertyVarName} ?? [] as \$item)
    //
@endforeach";
        }
            $result[] = "
@propertyNotEmpty('{$group}', '{$field}')
    // \${$propertyVarName} = ...
@endif";

        return $result;
    };
@endphp

<div>
    <x-filament-forms::field-wrapper.label class="pb-2">
        {{ __('inspirecms::resources/template.property_type_instructions.label') }}
    </x-filament-forms::field-wrapper.label>
    <div class="flex flex-col space-y-4 w-full">
        @foreach ($groupedPropertyTypes as $group => $propertyTypes)
        
            <x-filament::section compact>
                <x-slot name="heading">
                    {{ $group }}
                </x-slot>
                <x-slot name="headerEnd">
                    {{ __('inspirecms::resources/template.property_type_instructions.group') }}
                </x-slot>

                <div class="flex flex-col gap-y-2">
                    @foreach ($propertyTypes as $propertyType)
                        @php
                            $fieldType = $propertyType->config;

                            $fieldTypeConfig = $fieldType ? Arr::first($fieldType->getConfigNames()) : [];
                            $fieldTypeName = $fieldTypeConfig['name'] ?? null;
                            $icon = $fieldTypeConfig['icon'] ?? null;
                            $fieldKey = $propertyType->key;

                            $translatable = $fieldType?->isTranslatable() ?? false;
                        @endphp
                            
                        <x-filament::section 
                            collapsible
                            compact
                            x-tooltip="{
                                content: 'Field type: {{ $fieldTypeName }}',
                                placement: 'right',
                                theme: $store.theme,
                            }"  
                        >
                            <x-slot name="icon">
                                <x-filament::icon
                                    :icon="$icon"
                                    :alias="$icon"
                                    @class([
                                        'fi-section-header-icon self-start',
                                        'text-gray-400 dark:text-gray-500',
                                        'h-5 w-5 mt-0.5',
                                    ])
                                />
                            </x-slot>
                            <x-slot name="heading">
                                {{ $fieldKey }}
                            </x-slot>
                            <x-slot name="headerEnd">
                                @if ($translatable)
                                    <x-filament::icon-button
                                        icon="heroicon-o-language"
                                        color="gray"
                                        tooltip="{{ __('inspirecms::resources/field.translatable.label') }}"
                                    />
                                @endif
                                <span>
                                    {{ __('inspirecms::resources/template.property_type_instructions.field') }}
                                </span>
                            </x-slot>
                                
                            <ol class="flex gap-y-2 flex-col">
                                @foreach ($getPlainTextAndSampleCodeForField($fieldType, $propertyType->group, $fieldKey) as $text)
                                    <li class="flex gap-x-2 justify-between">
                                        <pre class="overflow-auto m-0 p-0">
                                            <code class="text-xs text-mono break-words">
                                                @php
                                                    $displayText = str($text)
                                                        ->trim() // Remove whitespace
                                                        ->prepend(PHP_EOL) // Add a newline at the start
                                                        ->toString();
                                                @endphp
                                                {{ $displayText }}
                                            </code>
                                        </pre>
                                        
                                        <x-inspirecms::buttons.copy-button
                                            :plaintext="$text"
                                        />
                                    </li>

                                    @if (!$loop->last)
                                        <hr class="border-gray-200 dark:border-gray-700">
                                    @endif
                                @endforeach
                            </ol>
                        </x-filament::section>
                    @endforeach
                </div>

            </x-filament::section>
        @endforeach
    </div>
</div>
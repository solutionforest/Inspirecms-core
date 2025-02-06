@php
    use Illuminate\Support\Arr;
    use SolutionForest\InspireCms\Dtos\PropertyTypeDto;
    use SolutionForest\InspireCms\Helpers\FieldTypeHelper;

    $groupedPropertyTypes = collect($getState())->map(function ($arr) {
        $data = $arr['dtoData'] ?? [];
        $data['config'] = FieldTypeHelper::getFieldTypeConfig($arr['fieldType'], $data['config'] ?? []);
        return PropertyTypeDto::fromArray($data);
    })->groupBy('group')->sortKeys();

    $getDisplayInstruction = fn ($text) => str($text)
        ->explode("\r\n")
        ->map(fn ($line) => str($line)
            ->replaceMatches('/\s\s+/', '&nbsp;&nbsp;')
            ->inlineMarkdown([
                'html_input' => 'escape',
                'allow_unsafe_links' => false,
            ])
        );
    $getPlainTextAndSampleCodeForField = function ($fieldType, $group, $field) use ($getDisplayInstruction) {

        $translatable = $fieldType?->isTranslatable() ?? false;

        $valueType = FieldTypeHelper::resolveFieldReturnType($fieldType);

        if ($valueType != 'array' || $translatable) {
            $result[] = "@property('{$group}', '{$field}')";
        } else {
            $result[] = "
@propertyArray('{$group}', '{$field}')
@foreach (\${$group}_{$field} ?? [] as \$item)
    //
@endforeach";
        }
            $result[] = "
@propertyNotEmpty('{$group}', '{$field}')
    // \${$group}_{$field} = ...
@endif";

        return $result;
    };
 
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
                            $fieldKey = $propertyType->key;

                            $translatable = $fieldType?->isTranslatable() ?? false;
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
                                {{ $fieldKey }}
                            </x-slot>
                            <x-slot name="headerEnd">
                                @if ($translatable)
                                    <x-filament::icon-button
                                        icon="heroicon-o-language"
                                        color="gray"
                                        tooltip="Translatable"
                                    />
                                @endif
                                <span>Field</span>
                            </x-slot>
                                
                            <ol class="flex gap-y-2 flex-col">
                                @foreach ($getPlainTextAndSampleCodeForField($fieldType, $propertyType->group, $fieldKey) as $text)
                                    <li class="flex gap-x-2 justify-between">
                                        <code class="text-xs">
                                            @foreach ($getDisplayInstruction($text) as $line)
                                                <div class="line whitespace-nowrap">
                                                    {!! $line !!}
                                                </div>
                                            @endforeach
                                        </code>
                                        
                                        <x-inspirecms::buttons.copy-button
                                            :plaintext="$text"
                                            :label="$copyButtonLabel"
                                            :message="$copiedMessage"
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
<?php

namespace SolutionForest\InspireCms\Fields\Configs;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\ConfigName;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\DbType;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\FormComponent;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig;
use SolutionForest\InspireCms\Fields\Configs\Attributes\Converter;
use SolutionForest\InspireCms\Fields\Converters\ContentPickerConverter;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentPicker as ContentPickerComponent;
use SolutionForest\InspireCms\Helpers\UIHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\DocumentType;

#[ConfigName('contentPicker', 'Content Picker', 'Picker', 'inspirecms::content_picker')]
#[FormComponent(ContentPickerComponent::class)]
#[DbType('mysql', 'varchar')]
#[DbType('sqlite', 'text')]
#[Converter(ContentPickerConverter::class)]
class ContentPicker extends FieldTypeBaseConfig implements FieldTypeConfig
{
    public ?int $max = null;

    public ?int $min = null;

    public ?string $documentType = null;

    public ?string $startNode = null;

    public function getFormSchema(): array
    {
        $documentTypeOptions = function ($component, $search) {
            $model = InspireCmsConfig::getDocumentTypeModelClass();

            return $model::query()
                ->take($component->getOptionsLimit())
                ->when(filled($search), function ($query) use ($search) {
                    $query->where('slug', 'like', "%$search%");
                })
                ->get()
                ->mapWithKeys(fn (DocumentType | Model $model) => [
                    $model->getKey() => UIHelper::generateTextWithBadge(
                        text: $model->title,
                        badgeText: $model->slug,
                        attributes: [
                            'text' => ['class' => 'flex-1 font-semibold'],
                            'badge' => ['class' => 'font-mono'],
                        ]
                    )->toHtml(),
                ])
                ->all();
        };

        return [
            Tabs::make('tabs')
                ->tabs([
                    Tab::make('Validation')
                        ->schema([
                            TextInput::make('min')->numeric(),
                            TextInput::make('max')->numeric(),
                        ]),
                    Tab::make('Presentation')
                        ->schema([
                            Select::make('documentType')
                                ->inlineLabel()
                                ->searchable()
                                ->optionsLimit(10)
                                ->allowHtml()
                                ->options(fn (Select $component) => $documentTypeOptions($component, null))
                                ->getSearchResultsUsing(fn (Select $component, $search) => $documentTypeOptions($component, $search)),
                            ContentPickerComponent::make('startNode')
                                ->inlineLabel()
                                ->maxItems(1)
                                ->filteringByPermission(false)
                                ->afterStateHydrated(function (ContentPickerComponent $component, $state) {
                                    if (! is_array($state)) {
                                        $state = [$state];
                                    }
                                    $state = collect($state)->flatten()->filter()->values()->all();
                                    $component->state($state);
                                })
                                ->dehydrateStateUsing(function ($state) {
                                    return collect($state)->filter()->first();
                                }),
                        ]),
                ]),
        ];
    }

    public function applyConfig(Component $component): void
    {
        if ($component instanceof ContentPickerComponent) {

            if ($this->documentType != null) {
                $component->where('document_type_id', $this->documentType);
            }

            if ($this->max) {
                $component->maxItems($this->max);
            }

            if ($this->min) {
                $component->minItems($this->min);
            }

            if ($this->startNode) {
                $component->startNode($this->startNode);
            }
        }
    }
}

<?php

namespace SolutionForest\InspireCms\Fields\Configs;

use Filament\Forms;
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

#[ConfigName('contentPicker', 'Content Picker', 'Picker', 'heroicon-o-squares-2x2')]
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
                ]);
        };

        return [
            Forms\Components\Tabs::make('tabs')
                ->tabs([
                    Forms\Components\Tabs\Tab::make('Validation')
                        ->schema([
                            Forms\Components\TextInput::make('min')->numeric(),
                            Forms\Components\TextInput::make('max')->numeric(),
                        ]),
                    Forms\Components\Tabs\Tab::make('Presentation')
                        ->schema([
                            Forms\Components\Select::make('documentType')
                                ->inlineLabel()
                                ->searchable()
                                ->optionsLimit(10)
                                ->allowHtml()
                                ->options(fn (Forms\Components\Select $component) => $documentTypeOptions($component, null))
                                ->getSearchResultsUsing(fn (Forms\Components\Select $component, $search) => $documentTypeOptions($component, $search))
                                ->live(),
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

    public function applyConfig(Forms\Components\Component $component): void
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

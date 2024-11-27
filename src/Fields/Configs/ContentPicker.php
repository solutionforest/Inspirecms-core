<?php

namespace SolutionForest\InspireCms\Fields\Configs;

use Filament\Forms;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\ConfigName;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\DbType;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\FormComponent;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentPicker as ContentPickerComponent;
use SolutionForest\InspireCms\InspireCmsConfig;

#[ConfigName('contentPicker', 'Content Picker', 'Picker', 'heroicon-o-pencil')]
#[FormComponent(ContentPickerComponent::class)]
#[DbType('mysql', 'varchar')]
#[DbType('sqlite', 'text')]
class ContentPicker extends FieldTypeBaseConfig implements FieldTypeConfig
{
    public ?string $perPage = null;

    public ?int $max = null;

    public ?int $min = null;

    public ?string $documentType = null;

    public function getFormSchema(): array
    {
        $documentTypeOptions = function ($component, $search) {
            $model = InspireCmsConfig::getDocumentTypeModelClass();

            return $model::query()
                ->limit($component->getOptionsLimit())
                ->when(filled($search), function ($query) use ($search) {
                    $query->where('slug', 'like', "%$search%");
                })
                ->get()
                ->mapWithKeys(fn ($model) => [
                    $model->getKey() => "<span class=\"font-bold\">{$model->title}</span><br/><span class=\"font-light\">{$model->slug}</span>",
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
                            Forms\Components\TextInput::make('perPage')
                                ->inlineLabel(),
                            Forms\Components\Select::make('documentType')
                                ->inlineLabel()
                                ->searchable()
                                ->optionsLimit(10)
                                ->allowHtml()
                                ->options(fn (Forms\Components\Select $component) => $documentTypeOptions($component, null))
                                ->getSearchResultsUsing(fn (Forms\Components\Select $component, $search) => $documentTypeOptions($component, $search))
                                ->live(),
                        ]),
                ]),
        ];
    }

    public function applyConfig(Forms\Components\Component $component): void
    {
        if ($component instanceof ContentPickerComponent) {

            $component->modifyPaginationOptionsUsing(function ($query) {
                if ($this->documentType) {
                    $query->where('document_type_id', $this->documentType);
                }

                return $query;
            });

            if ($this->perPage) {
                $component->perPage($this->perPage);
            }

            if ($this->max) {
                $component->maxItems($this->max);
            }

            if ($this->min) {
                $component->minItems($this->min);
            }
        }
    }
}

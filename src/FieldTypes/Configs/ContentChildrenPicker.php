<?php

namespace SolutionForest\InspireCms\FieldTypes\Configs;

use Filament\Forms;
use Filament\Tables;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\ConfigName;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\DbType;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\FormComponent;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig;
use SolutionForest\InspireCms\Filament\Forms\Components\PaginationPicker;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

#[ConfigName('contentChildrenPicker', 'Content Children Picker', 'Picker', 'heroicon-o-pencil')]
#[FormComponent(PaginationPicker::class)]
#[DbType('mysql', 'varchar')]
#[DbType('sqlite', 'text')]
class ContentChildrenPicker extends FieldTypeBaseConfig implements FieldTypeConfig
{
    public ?string $perPage = null;

    public ?int $max = null;

    public ?int $min = null;

    public ?string $parentDocumentType = null;

    public ?string $parentContent = null;

    public ?string $template = null;

    public function getFormSchema(): array
    {
        $documentTypeOptions = function (Forms\Components\Select $component, $search) {
            $model = InspireCmsConfig::getDocumentTypeModelClass();

            return $model::query()
                ->limit($component->getOptionsLimit())
                ->when(filled($search), function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%");
                })
                ->get()
                ->mapWithKeys(fn ($model) => [
                    $model->getKey() => $model->name,
                ]);
        };
        $contentOptions = function ($documentTypeId, Forms\Components\Select $component, $search) {
            $model = InspireCmsConfig::getContentModelClass();

            return $model::query()
                ->limit($component->getOptionsLimit())
                ->where('document_type_id', $documentTypeId)
                ->when(filled($search), function ($query) use ($search) {
                    $query
                        ->where('title', 'like', "%$search%")
                        ->orWhere('slug', 'like', "%$search%");
                })
                ->get()
                ->mapWithKeys(fn ($model) => [
                    $model->getKey() => $model->title,
                ]);
        };
        $templateOptions = function ($documentTypeId) {
            $model = InspireCmsConfig::getTemplateModelClass();

            if (! $documentTypeId) {
                return [];
            }

            return $model::query()
                ->whereHas(
                    'documentTypes',
                    fn ($q) => $q->where('templateable_id', $documentTypeId)
                )
                ->get()
                ->mapWithKeys(fn ($model) => [
                    $model->getKey() => $model->name,
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
                            Forms\Components\Select::make('parentDocumentType')
                                ->inlineLabel()
                                ->searchable()
                                ->optionsLimit(20)
                                ->options(fn (Forms\Components\Select $component) => $documentTypeOptions($component, null))
                                ->getSearchResultsUsing(fn (Forms\Components\Select $component, $search) => $documentTypeOptions($component, $search))
                                ->live(),
                            Forms\Components\Select::make('parentContent')
                                ->inlineLabel()
                                ->searchable()
                                ->optionsLimit(20)
                                ->options(fn (Forms\Components\Select $component, Forms\Get $get) => $contentOptions($get('parentDocumentType'), $component, null))
                                ->getSearchResultsUsing(fn (Forms\Components\Select $component, Forms\Get $get, $search) => $contentOptions($get('parentDocumentType'), $component, $search)),
                            Forms\Components\Select::make('template')
                                ->inlineLabel()
                                ->searchable()
                                ->options(fn (Forms\Get $get) => $templateOptions($get('parentDocumentType'))),
                        ]),
                ]),
        ];
    }

    public function applyConfig(Forms\Components\Component $component): void
    {
        if ($component instanceof PaginationPicker) {

            $model = InspireCmsConfig::getContentModelClass();

            $query = $model::query();

            if ($this->parentContent) {
                $query->whereHas(
                    'parent',
                    fn ($q) => $q
                        ->where('id', $this->parentContent)
                        ->when($this->parentDocumentType, fn ($q) => $q->where('document_type_id', $this->parentDocumentType))
                );
            }

            $component->paginationOptions($query);

            $component->recordTitleUsing(fn ($record) => $record->title);
            
            if ($this->perPage) {
                $component->perPage($this->perPage);
            }

            $component
                ->tableColumns([
                    Tables\Columns\TextColumn::make('id'),
                    Tables\Columns\TextColumn::make('title'),
                    Tables\Columns\TextColumn::make('slug'),
                ]);

            if ($this->max) {
                $component->maxItems($this->max);
            }

            if ($this->min) {
                $component->minItems($this->min);
            }

        }
    }
}

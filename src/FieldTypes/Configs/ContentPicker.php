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

#[ConfigName('contentPicker', 'Content Picker', 'Picker', 'heroicon-o-pencil')]
#[FormComponent(PaginationPicker::class)]
#[DbType('mysql', 'varchar')]
#[DbType('sqlite', 'text')]
class ContentPicker extends FieldTypeBaseConfig implements FieldTypeConfig
{
    public ?string $perPage = null;

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Tabs::make('tabs')
                ->tabs([
                    Forms\Components\Tabs\Tab::make('Presentation')
                        ->schema([
                            Forms\Components\TextInput::make('perPage')
                                ->inlineLabel(),
                        ]),
                ]),
        ];
    }

    public function applyConfig(Forms\Components\Component $component): void
    {
        if ($component instanceof PaginationPicker) {

            $model = InspireCmsConfig::getContentModelClass();

            $component->paginationOptions($model::query());

            if ($this->perPage) {
                $component->perPage($this->perPage);
            }

            $component
                ->tableColumns([
                    Tables\Columns\TextColumn::make('id'),
                    Tables\Columns\TextColumn::make('title'),
                    Tables\Columns\TextColumn::make('slug'),
                ]);

        }
    }
}

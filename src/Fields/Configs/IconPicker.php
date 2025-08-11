<?php

namespace SolutionForest\InspireCms\Fields\Configs;

use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\ConfigName;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\DbType;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Attributes\FormComponent;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\FieldTypeBaseConfig;
use SolutionForest\InspireCms\Fields\Configs\Concerns\HasColumnsLayoutConfig;
use SolutionForest\InspireCms\Filament\Forms\Components\IconPicker as FormsIconPicker;

#[ConfigName('iconPicker', 'Icon Picker', 'Picker', 'heroicon-o-cog')]
#[FormComponent(FormsIconPicker::class)]
#[DbType('mysql', 'varchar')]
#[DbType('sqlite', 'text')]
class IconPicker extends FieldTypeBaseConfig implements FieldTypeConfig
{
    use HasColumnsLayoutConfig;

    public function getFormSchema(): array
    {
        return [
            Tabs::make('tabs')
                ->tabs([
                    Tab::make('Presentation')
                        ->schema([
                            static::getHasColumnsLayoutConfigComponent(),
                        ]),
                ]),
        ];
    }

    public function applyConfig(Component $component): void
    {
        if ($component instanceof FormsIconPicker) {
            if (is_array($this->columnsLayout) && ($filterColumns = $this->filterColumnsData($this->columnsLayout)) && ! empty($filterColumns)) {
                $component->columns($filterColumns);
            }
        }
    }

    private function filterColumnsData(array $data): array
    {
        return collect($data)
            ->filter(fn ($value, $key) => is_numeric($value) && $value > 0 && in_array($key, ['default', 'sm', 'md', 'lg', 'xl']))
            ->toArray();
    }
}

<?php

namespace SolutionForest\InspireCms\Filament\Resources\Exports\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use SolutionForest\InspireCms\Exports\Exporters\BaseExporter;
use SolutionForest\InspireCms\Helpers\ExportDataHelper;

class ExportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Select::make('exporter')
                    ->options(ExportDataHelper::getExporters())
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Select $component) => $component
                        ->getContainer()
                        ->getComponent('dynamicTypeFields')
                        ->getChildComponentContainer()
                        ->fill()),

                Group::make()
                    ->statePath('payload.args')
                    ->key('dynamicTypeFields')
                    ->dehydrated(true)
                    ->schema(function ($get) {

                        $exporter = $get('exporter');

                        if ($exporter && is_string($exporter) && class_exists($exporter) && is_a($exporter, BaseExporter::class, true)) {
                            $fields = $exporter::getArgsFormFields();

                            return $fields;
                        }

                        return [];
                    }),
            ]);
    }
}

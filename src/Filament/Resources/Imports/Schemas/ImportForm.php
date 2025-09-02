<?php

namespace SolutionForest\InspireCms\Filament\Resources\Imports\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Schema;
use SolutionForest\InspireCms\Filament\Resources\Imports\Actions\DownloadSampleImportAction;
use SolutionForest\InspireCms\Helpers\ImportDataHelper;

class ImportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([

                Hidden::make('file_disk'),

                DateTimePicker::make('available_at')
                    ->label(__('inspirecms::resources/import.available_at.label'))
                    ->validationAttribute(__('inspirecms::resources/import.available_at.validation_attribute'))
                    ->helperText(__('inspirecms::resources/import.available_at.instructions'))
                    ->hint(__('inspirecms::resources/import.available_at.hint'))
                    ->native(false)
                    ->autofocus(false),

                FileUpload::make('file_name')
                    ->required()
                    ->label(__('inspirecms::resources/import.file_name.label'))
                    ->validationAttribute(__('inspirecms::resources/import.file_name.validation_attribute'))
                    ->hint(__('inspirecms::resources/import.file_name.hint'))
                    ->disk(ImportDataHelper::getDiskDriver())
                    ->directory(ImportDataHelper::getDirectory())
                    ->acceptedFileTypes(ImportDataHelper::getAllowedMimeTypes())
                    ->preserveFilenames(false)
                    ->belowContent(
                        Schema::end(
                            DownloadSampleImportAction::make(),
                        )
                    ),

                Placeholder::make('file_structure_instructions')
                    ->label(__('inspirecms::resources/import.file_structure_instructions.label'))
                    ->hint(__('inspirecms::resources/import.file_structure_instructions.hint'))
                    ->hintColor('warning')
                    ->content(view('inspirecms::import.file-structure-sample', [
                        'structure' => ImportDataHelper::getSampleFileStructure(),
                    ]))
                    ->columnSpanFull(),
            ]);
    }
}

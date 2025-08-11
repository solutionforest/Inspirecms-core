<?php

namespace SolutionForest\InspireCms\Filament\Resources\Templates\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use SolutionForest\InspireCms\Helpers\TemplateHelper;

class TemplateInfoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextEntry::make('exported_content_template_directory')
                    ->label(__('inspirecms::inspirecms.exported_content_template_directory'))
                    ->state(function () {

                        $fullPath = TemplateHelper::getDirectoryForExportedTemplates();

                        return str($fullPath ? str_replace(base_path(), '', $fullPath) : '')
                            ->replace('\\', '/')
                            ->trim('/')
                            ->toString();
                    })
                    ->fontFamily(FontFamily::Mono)
                    ->size('xs')
                    ->placeholder(fn () => strval(__('inspirecms::inspirecms.n/a')))
                    ->extraAttributes(['class' => 'overflow-x-auto overflow-y-hidden']),
            ]);
    }
}

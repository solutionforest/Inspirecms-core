<?php

namespace SolutionForest\InspireCms\Filament\Resources\DocumentTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Filament\Forms\Components\IconPicker;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypes\Schemas\Components\DocumentTypeAllowedDocumentType;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypes\Schemas\Components\DocumentTypeCategorySwitcher;
use SolutionForest\InspireCms\InspireCmsConfig;

class DocumentTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Section::make()
                    ->heading(__('inspirecms::resources/document-type.sections.general.heading'))
                    ->columns(1)
                    ->aside()
                    ->schema([
                        static::getTitleFormComponent()->inlineLabel(),
                        static::getSlugFormComponent()->inlineLabel(),
                        static::getShowAsTableFormComponent(),
                        DocumentTypeCategorySwitcher::make()->inlineLabel(),
                        static::getIconFormComponent()->inlineLabel(),
                    ]),
                Section::make()
                    ->heading(__('inspirecms::resources/document-type.sections.display.heading'))
                    ->description(__('inspirecms::resources/document-type.sections.display.description'))
                    ->columns(1)
                    ->aside()
                    ->schema(
                        collect([
                            static::getShowAtRootFormComponent(),
                        ])
                            ->when(
                                $schema->getOperation() == 'edit',
                                fn ($collection) => $collection
                                    ->push(DocumentTypeAllowedDocumentType::make())
                            )
                            ->all()
                    ),
            ]);
    }

    protected static function getTitleFormComponent()
    {
        return TextInput::make('title')
            ->label(__('inspirecms::resources/document-type.title.label'))
            ->validationAttribute(__('inspirecms::resources/document-type.title.validation_attribute'))
            ->required()
            ->live(true, 5000)
            ->afterStateUpdated(function ($component, $state, $get, $set, $operation) {
                // Fill slug if empty / operation is create
                if ($operation === 'create' || empty($get('slug'))) {
                    $set('slug', Str::slug($state));
                }
            });
    }

    protected static function getSlugFormComponent()
    {
        return TextInput::make('slug')
            ->label(__('inspirecms::resources/document-type.slug.label'))
            ->validationAttribute(__('inspirecms::resources/document-type.slug.validation_attribute'))
            ->unique(table: InspireCmsConfig::getDocumentTypeModelClass(), column: 'slug', ignoreRecord: true)
            ->autofocus()
            ->required()
            ->live(true, 5000)
            ->afterStateUpdated(function ($component, $state) {
                return $component->state(Str::slug($state));
            });
    }

    protected static function getIconFormComponent()
    {
        return IconPicker::make('icon')
            ->label(__('inspirecms::resources/document-type.icon.label'))
            ->validationAttribute(__('inspirecms::resources/document-type.icon.validation_attribute'));
    }

    protected static function getShowAtRootFormComponent()
    {
        return Toggle::make('show_at_root')
            ->label(__('inspirecms::resources/document-type.show_at_root.label'))
            ->validationAttribute(__('inspirecms::resources/document-type.show_at_root.validation_attribute'))
            ->inlineLabel()
            ->default(true);
    }

    protected static function getShowAsTableFormComponent()
    {
        return Toggle::make('show_as_table')
            ->label(__('inspirecms::resources/document-type.show_as_table.label'))
            ->validationAttribute(__('inspirecms::resources/document-type.show_as_table.validation_attribute'))
            ->inlineLabel()
            ->default(false)
            ->live();
    }
}

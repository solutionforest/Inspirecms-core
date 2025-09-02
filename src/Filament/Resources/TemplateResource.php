<?php

namespace SolutionForest\InspireCms\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Resources\TemplateResource\Pages\ListTemplates;
use SolutionForest\InspireCms\Filament\Resources\Templates\Schemas\TemplateSimpleEditorForm;
use SolutionForest\InspireCms\Filament\Resources\Templates\Tables\TemplatesTable;
use SolutionForest\InspireCms\InspireCmsConfig;

class TemplateResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?int $navigationSort = -5;

    protected static ?string $cluster = Settings::class;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'view',
            'create',
            'update',
            'delete',
            'delete_any',
            'attach',
            'detach',
        ];
    }

    public static function getNavigationIcon(): string | Htmlable | null
    {
        return FilamentIcon::resolve('inspirecms::templates');
    }

    public static function form(Schema $schema): Schema
    {
        return TemplateSimpleEditorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TemplatesTable::configure($table)
            ->modifyQueryUsing(fn ($query) => $query->with(['documentTypes', 'contents' => fn ($query) => $query->withoutGlobalScopes([SoftDeletingScope::class])]))
            ->heading(static::getNavigationLabel());
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTemplates::route('/'),
        ];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getTemplateModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.template.singular');
    }

    // region Global search
    public static function canGloballySearch(): bool
    {
        return false;
    }
    // endregion Global search
}

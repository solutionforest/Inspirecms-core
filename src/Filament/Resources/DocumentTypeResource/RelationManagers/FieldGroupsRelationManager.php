<?php

namespace SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Concerns\CanAuthorizeRelationManager;
use SolutionForest\InspireCms\Filament\Resources\FieldGroups\Schemas\FieldGroupInfolist;
use SolutionForest\InspireCms\Filament\Resources\FieldGroups\Tables\RelatedFieldGroupsTable;

class FieldGroupsRelationManager extends RelationManager
{
    use CanAuthorizeRelationManager;

    protected static string $relationship = 'fieldGroups';

    protected static ?string $inverseRelationship = 'documentTypes';

    protected $listeners = [
        'refreshFieldGroups' => '$refresh',
    ];

    public function infolist(Schema $schema): Schema
    {
        return FieldGroupInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        return RelatedFieldGroupsTable::fromDocumentType($table);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('inspirecms::resources/document-type.field_groups.label');
    }
}

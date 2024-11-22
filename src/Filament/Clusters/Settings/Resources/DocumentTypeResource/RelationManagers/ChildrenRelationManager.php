<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\DocumentTypeResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Base\Filament\RelationManagers\BaseChildrenRelationManager;
use SolutionForest\InspireCms\Models\Contracts\DocumentType;

class ChildrenRelationManager extends BaseChildrenRelationManager
{
    protected static string $relationship = 'children';

    protected static ?string $inverseRelationship = 'parent';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        if ($ownerRecord instanceof DocumentType) {
            return $ownerRecord->canBeParent();
        }

        return false;
    }

    public function form(Form $form): Form
    {
        $resource = $this->getPageClass()::getResource();

        return $resource::childrenForm($form, $this->getOwnerRecord());
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->openRecordUrlInNewTab();
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        if (is_null($ownerRecord->children_count)) {
            $ownerRecord->loadCount('children');
        }

        return $ownerRecord->children_count;
    }

    protected function isRedirectToCreatePage(): bool
    {
        return false;
    }

    protected function isRedirectToDetailPage(): bool
    {
        return true;
    }

    protected function configureEditAction(Tables\Actions\EditAction $action): void
    {
        parent::configureEditAction($action);

        $action->openUrlInNewTab();
    }
}

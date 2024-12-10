<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource\RelationManagers;

use Filament\Resources\RelationManagers\Concerns\Translatable;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Reactive;
use SolutionForest\InspireCms\Base\Filament\RelationManagers\BaseChildrenRelationManager;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentFormTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\ContentForm;
use SolutionForest\InspireCms\Filament\Tables\Actions\CreateContentAction;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Models\Contracts\Content;

class ChildrenRelationManager extends BaseChildrenRelationManager implements ContentForm
{
    use ContentFormTrait;
    use Translatable;

    #[Reactive]
    public ?string $activeLocale = null;

    protected static ?string $recordTitleAttribute = 'title';

    public function getTranslatableLocales(): array
    {
        return array_keys(InspireCms::getAllAvailableLanguages());
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        if (! parent::canViewForRecord($ownerRecord, $pageClass)) {
            return false;
        }
        if (! $ownerRecord instanceof Content) {
            return false;
        }

        return $ownerRecord->documentType?->show_as_table === true;
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->headerActions([
                CreateContentAction::make()
                    ->parentContentKey(fn () => $this->getOwnerRecord()->getKey())
                    ->parentDocumentType(fn () => $this->getOwnerRecord()->documentType),
            ]);
    }

    public function getDocumentType(): int | Model | string
    {
        return $this->getOwnerRecord()->documentType;
    }

    public function getParent(): int | Model | string | null
    {
        return $this->getOwnerRecord()->parent;
    }

    public function getParentKey(): string | int | null
    {
        return $this->getOwnerRecord()->parent_id;
    }

    protected function configureEditAction(Tables\Actions\EditAction $action): void
    {
        parent::configureEditAction($action);

        $resource = $this->getPageClass()::getResource();

        $action->url(
            fn ($record) => FilamentResourceHelper::attemptToGetUrl($resource, 'edit', ['record' => $record, ...$this->getRedirectUrlParameters()], false)
        );
    }

    protected function configureViewAction(Tables\Actions\ViewAction $action): void
    {
        parent::configureViewAction($action);

        $resource = $this->getPageClass()::getResource();

        $action->url(
            fn ($record) => FilamentResourceHelper::attemptToGetUrl($resource, 'view', ['record' => $record, ...$this->getRedirectUrlParameters()], false)
        );
    }

    protected function getRedirectUrlParameters(): array
    {
        return [
            'activeRelationManager' => 0,
            'locale' => $this->activeLocale,
        ];
    }
}

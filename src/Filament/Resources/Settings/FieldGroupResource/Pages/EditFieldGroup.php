<?php

namespace SolutionForest\InspireCms\Filament\Resources\Settings\FieldGroupResource\Pages;

use Filament\Actions\Action;
use SolutionForest\InspireCms\Filament\Resources\Pages\EditWithDetailInfoPage;
use SolutionForest\InspireCms\Filament\Resources\Settings\FieldGroupResource;

class EditFieldGroup extends EditWithDetailInfoPage
{
    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label(__('inspirecms::inspirecms.actions.save.label'));
    }

    public static function getResource(): string
    {
        return config('inspirecms.resources.field_group', FieldGroupResource::class);
    }

    public function wrapMainFormBySection(): bool
    {
        return false;
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabIcon(): ?string
    {
        return 'heroicon-m-cog';
    }

    public function getContentTabLabel(): ?string
    {
        return $this->getTitle();
    }
}

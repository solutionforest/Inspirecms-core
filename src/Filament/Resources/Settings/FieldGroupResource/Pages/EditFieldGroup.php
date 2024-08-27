<?php

namespace SolutionForest\InspireCms\Filament\Resources\Settings\FieldGroupResource\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;
use SolutionForest\InspireCms\Filament\Resources\Settings\FieldGroupResource;

class EditFieldGroup extends EditRecord
{
    public function getFormActionsAlignment(): string | Alignment
    {
        return Alignment::End;
    }
    
    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label(__('inspirecms::inspirecms.actions.save.label'));
    }

    public static function getResource(): string
    {
        return config('inspirecms.resources.field_group', FieldGroupResource::class);
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

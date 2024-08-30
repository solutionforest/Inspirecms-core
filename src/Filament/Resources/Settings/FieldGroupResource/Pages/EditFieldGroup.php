<?php

namespace SolutionForest\InspireCms\Filament\Resources\Settings\FieldGroupResource\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;
use Illuminate\Contracts\Support\Htmlable;
use SolutionForest\InspireCms\Filament\Resources\Settings\FieldGroupResource;

class EditFieldGroup extends EditRecord
{
    public function getFormActionsAlignment(): string | Alignment
    {
        return 'end';
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label(__('inspirecms::actions.save.label'));
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
        return null;
    }

    public function getContentTabLabel(): ?string
    {
        return __('inspirecms::inspirecms.general');
    }

    public function getHeading(): string
    {
        return parent::getTitle();
    }

    public function getSubheading(): ?string
    {
        return $this->getRecordSubTitle();
    }

    public function getRecordSubTitle(): null | string | Htmlable
    {
        $resource = static::getResource();

        if (! method_exists($resource, 'getRecordSubTitle')) {
            return null;
        }

        return $resource::getRecordSubTitle($this->getRecord());
    }
}

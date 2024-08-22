<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents\PageResource\Pages;

use Filament\Actions\Action;
use SolutionForest\InspireCms\Filament\Resources\Contents\PageResource;
use SolutionForest\InspireCms\Filament\Resources\Pages\CreateWithDetailInfoPage;

class CreatePage extends CreateWithDetailInfoPage
{
    protected static bool $canCreateAnother = false;

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label(__('inspirecms::inspirecms.actions.save.label'));
    }

    public static function getResource(): string
    {
        return config('inspirecms.resources.page', PageResource::class);
    }

    public function wrapDetailInfoFormBySection(): bool
    {
        return false;
    }

    protected function getDetailInfoFormStatePath(): string
    {
        return 'data';
    }

    public function afterFill()
    {
        $this->detailInfoForm?->fill();
    }
}

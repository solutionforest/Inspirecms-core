<?php

namespace SolutionForest\InspireCms\Filament\Resources\ContentResource\Pages;

use Filament\Actions\Action;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseContentListTrashPage;
use SolutionForest\InspireCms\Filament\Actions\BackToParentContentAction;
use SolutionForest\InspireCms\Filament\Resources\ContentResource;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\InspireCmsConfig;

class ListTrashedContentRecords extends BaseContentListTrashPage
{
    public function getActions(): array
    {
        return [
            BackToParentContentAction::make()
                ->button()
                ->url(fn () => FilamentResourceHelper::attemptToGetUrl(static::getResource(), 'index', [], false)),
            ...parent::getActions(),
        ];
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('content', ContentResource::class);
    }

    public function getDefaultActionSuccessRedirectUrl(Action $action): ?string
    {
        return $this->getUrl();
    }

    public function getBreadcrumb(): ?string
    {
        return __('inspirecms::inspirecms.trash_bin');
    }

    public function getTitle(): string
    {
        return __('inspirecms::inspirecms.trash_bin');
    }
}

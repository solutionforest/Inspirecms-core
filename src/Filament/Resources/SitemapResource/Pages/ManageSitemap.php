<?php

namespace SolutionForest\InspireCms\Filament\Resources\SitemapResource\Pages;

use Filament\Actions;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseManageRecords;
use SolutionForest\InspireCms\Filament\Resources\SitemapResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class ManageSitemap extends BaseManageRecords
{
    public function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('sitemap', SitemapResource::class);
    }
}

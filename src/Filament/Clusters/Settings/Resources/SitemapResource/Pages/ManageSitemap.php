<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\SitemapResource\Pages;

use Filament\Actions;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseManagePage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\SitemapResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class ManageSitemap extends BaseManagePage
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

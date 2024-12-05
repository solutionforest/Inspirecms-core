<?php

namespace SolutionForest\InspireCms\Filament\Widgets;

use Filament\Widgets\Widget;

class CmsInfoWidget extends Widget
{
    protected static string $view = 'inspirecms::filament.widgets.cms-info';

    protected int | string | array $columnSpan = 'full';

    protected static bool $isLazy = false;

    public function getDocumentUrl(): string
    {
        return 'https://docs.inspirecms.io';
    }

    public function getNewsUrl(): string
    {
        return 'https://inspirecms.io/news';
    }

    public function getLightScreenShotUrl(): string
    {
        return 'https://laravel.com/assets/img/welcome/docs-light.svg';
    }

    public function getDarkScreenShotUrl(): string
    {
        return 'https://laravel.com/assets/img/welcome/docs-dark.svg';
    }
}

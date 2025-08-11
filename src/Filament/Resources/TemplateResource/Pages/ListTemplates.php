<?php

namespace SolutionForest\InspireCms\Filament\Resources\TemplateResource\Pages;

use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListRecords;
use SolutionForest\InspireCms\Filament\Resources\TemplateResource;
use SolutionForest\InspireCms\Filament\Widgets\TemplateInfo;
use SolutionForest\InspireCms\Filament\Widgets\ThemeInfo;
use SolutionForest\InspireCms\InspireCmsConfig;

class ListTemplates extends BaseListRecords
{
    public ?string $theme = null;

    public function mount(): void
    {
        parent::mount();

        $this->theme = inspirecms_templates()->getCurrentTheme();
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('template', TemplateResource::class);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ThemeInfo::class,
            TemplateInfo::class,
        ];
    }
}

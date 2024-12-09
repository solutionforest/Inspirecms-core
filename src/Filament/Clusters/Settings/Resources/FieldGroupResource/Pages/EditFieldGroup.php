<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldGroupResource\Pages;

use Illuminate\Contracts\Support\Htmlable;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseEditPage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\FieldGroupResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class EditFieldGroup extends BaseEditPage
{
    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('field_group', FieldGroupResource::class);
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

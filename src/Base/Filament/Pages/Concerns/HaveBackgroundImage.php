<?php

namespace SolutionForest\InspireCms\Base\Filament\Pages\Concerns;

use SolutionForest\InspireCms\InspireCmsConfig;

trait HaveBackgroundImage
{
    protected function getBackgroundImage()
    {
        $image = InspireCmsConfig::get('admin.background_image');

        if (is_array($image) && count($image) > 0) {
            return $image[array_rand($image)];
        }

        return $image;
    }

    protected function getLayoutData(): array
    {
        return [
            ...parent::getLayoutData(),
            'image' => $this->getBackgroundImage(),
        ];
    }
}

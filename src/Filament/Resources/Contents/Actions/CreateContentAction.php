<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents\Actions;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Actions\CreateContentAction as ActionsCreateContentAction;
use SolutionForest\InspireCms\Filament\Resources\ContentResource;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\InspireCmsConfig;

class CreateContentAction
{
    public static function make(): Action
    {
        return ActionsCreateContentAction::make();
    }

    public static function generateCreateContentUrl(null | Model | string | int $documentType, null | Model | string | int $parentContent, ?string $translatableLocale)
    {
        $parameters = [
            'documentType' => $documentType && $documentType instanceof Model ? $documentType->getKey() : $documentType,
            'parent' => $parentContent && $parentContent instanceof Model ? $parentContent->getKey() : $parentContent,
            // Set the locale as query parameter as ContentPageTrait
            'locale' => $translatableLocale,
        ];

        return FilamentResourceHelper::attemptToGetUrl(
            InspireCmsConfig::getFilamentResource('content', ContentResource::class),
            'create',
            $parameters,
            false
        );
    }
}

<?php

namespace SolutionForest\InspireCms\Filament\Widgets\Conceners;

use Illuminate\Support\Str;
use SolutionForest\InspireCms\Facades\PermissionManifest;

trait GuardWidgetTrait
{
    public static function canView(): bool
    {
        $authResult = PermissionManifest::authorizeWidget(static::class);

        if (is_bool($authResult)) {
            return $authResult;
        }

        return parent::canView();
    }
    
    public static function getPermissionName(): string
    {
        return Str::of(static::class)->classBasename()->snake()->prepend('widgets_')->toString();
    }

    public static function getPermissionDisplayName(): string
    {
        return Str::of(static::class)->classBasename()->snake()->replace('_', ' ')->title()->toString();
    }
}

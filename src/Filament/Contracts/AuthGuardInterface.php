<?php

namespace SolutionForest\InspireCms\Filament\Contracts;

interface AuthGuardInterface
{
    public static function getPermissionName(): string;

    public static function getPermissionDisplayName(): string;
}

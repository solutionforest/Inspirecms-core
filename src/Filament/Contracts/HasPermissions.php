<?php

namespace SolutionForest\InspireCms\Filament\Contracts;

interface HasPermissions
{
    public static function getPermissionPrefixes(): array;
}

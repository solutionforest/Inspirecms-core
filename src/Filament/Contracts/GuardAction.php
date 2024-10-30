<?php

namespace SolutionForest\InspireCms\Filament\Contracts;

interface GuardAction
{
    public static function getPermissionName(): string;

    public static function getPermissionDisplayName(): string;
}

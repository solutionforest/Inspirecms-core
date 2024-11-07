<?php

namespace SolutionForest\InspireCms\Filament\Contracts;

interface GuardPage
{
    public static function getPermissionName(): string;

    public static function getPermissionDisplayName(): string;
}

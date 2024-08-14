<?php

namespace SolutionForest\InspireCms\Filament\Resources\Settings;

use SolutionForest\FilamentFieldGroup\Models\FieldGroup;
use SolutionForest\InspireCms\Filament\Resources\Settings\FieldGroupResource\Pages;
use SolutionForest\FilamentFieldGroup\Filament\Resources\FieldGroupResource as BaseResource;

class FieldGroupResource extends BaseResource
{
    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageFieldGroup::route('/'),
            'edit' => Pages\EditFieldGroup::route('/{record}/edit'),
        ];
    }

    public static function getModel(): string
    {
        return config('filament-field-group.models.field_group', FieldGroup::class);
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms-core::inspirecms-core.field_group');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('inspirecms-core::inspirecms-core.setting');
    }
}

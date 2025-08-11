<?php

namespace SolutionForest\InspireCms\Filament\Resources\FieldGroups\Schemas;

use Filament\Schemas\Schema;
use SolutionForest\InspireCms\Filament\Resources\FieldGroups\Schemas\Components\FieldGroupActiveToggle;
use SolutionForest\InspireCms\Filament\Resources\FieldGroups\Schemas\Components\FieldGroupNameInput;
use SolutionForest\InspireCms\Filament\Resources\FieldGroups\Schemas\Components\FieldGroupTitleInput;

class FieldGroupReplicateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                FieldGroupNameInput::make(),
                FieldGroupTitleInput::make(),
                FieldGroupActiveToggle::make(),
            ]);
    }
}

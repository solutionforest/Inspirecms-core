<?php

namespace SolutionForest\InspireCmsApi\Filament\Resources\ApiTokenResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use SolutionForest\InspireCmsApi\Filament\Resources\ApiTokenResource;

class EditApiToken extends EditRecord
{
    protected static string $resource = ApiTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

<?php

namespace SolutionForest\InspireCmsApi\Filament\Resources\ApiTokenResource\Pages;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use SolutionForest\InspireCmsApi\Filament\Resources\ApiTokenResource;

class CreateApiToken extends CreateRecord
{
    protected static string $resource = ApiTokenResource::class;

    protected ?string $generatedToken = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate the token
        $plainToken = \Illuminate\Support\Str::random(40);
        $hashedToken = hash(config('inspirecms-api.auth.token_hash_algo', 'sha256'), $plainToken);

        $data['token'] = $hashedToken;
        $this->generatedToken = $plainToken;

        return $data;
    }

    protected function afterCreate(): void
    {
        // Show the generated token to the user
        Notification::make()
            ->title('API Token Created')
            ->body("Your new API token: **{$this->generatedToken}**\n\nPlease copy this token now. You won't be able to see it again!")
            ->success()
            ->persistent()
            ->send();
    }
}

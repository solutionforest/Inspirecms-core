<?php

namespace SolutionForest\InspireCmsApi\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use SolutionForest\InspireCmsApi\Filament\Resources\ApiTokenResource\Pages;
use SolutionForest\InspireCmsApi\Models\ApiToken;

class ApiTokenResource extends Resource
{
    protected static ?string $model = ApiToken::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'API Tokens';

    protected static ?int $navigationSort = 100;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Token Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Token Name')
                            ->helperText('A descriptive name for this token (e.g., "Mobile App", "External Service")'),

                        Forms\Components\Select::make('user_id')
                            ->label('Associated User')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Optional: Associate this token with a specific user for tracking purposes.'),

                        Forms\Components\CheckboxList::make('abilities')
                            ->label('Abilities')
                            ->options([
                                '*' => 'Full Access (all operations)',
                                'read' => 'Read Only (GET requests)',
                                'write' => 'Write (POST, PUT, PATCH)',
                                'delete' => 'Delete (DELETE requests)',
                            ])
                            ->default(['*'])
                            ->columns(2)
                            ->helperText('Select what this token is allowed to do.'),

                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expires At')
                            ->helperText('Leave empty for a non-expiring token.')
                            ->native(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->placeholder('No user')
                    ->sortable(),

                Tables\Columns\TextColumn::make('abilities')
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        if (is_array($state) && in_array('*', $state)) {
                            return 'Full Access';
                        }

                        return is_array($state) ? implode(', ', $state) : $state;
                    }),

                Tables\Columns\TextColumn::make('last_used_at')
                    ->label('Last Used')
                    ->dateTime()
                    ->placeholder('Never')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime()
                    ->placeholder('Never')
                    ->sortable()
                    ->color(fn ($record) => $record->isExpired() ? 'danger' : null),

                Tables\Columns\IconColumn::make('is_valid')
                    ->label('Status')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->isValid()),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('valid')
                    ->label('Valid Tokens')
                    ->query(fn ($query) => $query->valid()),

                Tables\Filters\Filter::make('expired')
                    ->label('Expired Tokens')
                    ->query(fn ($query) => $query->expired()),
            ])
            ->actions([
                Tables\Actions\Action::make('regenerate')
                    ->label('Regenerate')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Regenerate Token')
                    ->modalDescription('This will generate a new token. The old token will stop working immediately.')
                    ->action(function (ApiToken $record) {
                        $newTokenData = ApiToken::createToken(
                            name: $record->name,
                            userId: $record->user_id,
                            abilities: $record->abilities ?? ['*'],
                            expiryDays: $record->expires_at ? now()->diffInDays($record->expires_at) : null
                        );

                        $record->delete();

                        Notification::make()
                            ->title('Token Regenerated')
                            ->body("New token: {$newTokenData['plain_token']}")
                            ->success()
                            ->persistent()
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApiTokens::route('/'),
            'create' => Pages\CreateApiToken::route('/create'),
            'edit' => Pages\EditApiToken::route('/{record}/edit'),
        ];
    }
}

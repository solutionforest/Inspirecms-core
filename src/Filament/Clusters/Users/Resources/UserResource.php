<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Users\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use SolutionForest\InspireCms\Filament\Clusters\Users;
use SolutionForest\InspireCms\Filament\Clusters\Users\Resources\UserResource\Pages;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Forms\Components\UserRolePicker;
use SolutionForest\InspireCms\Models\Contracts\User;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class UserResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $cluster = Users::class;

    public static function createForm(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        static::getNameFormComponent(),
                        static::getEmailFormComponent(),
                        static::getPasswordFormComponent(),
                        static::getPasswordConfirmationFormComponent(),
                    ])
                    ->columnSpan(2),
                Forms\Components\Section::make()
                    ->schema([
                        static::getRolesFormComponent(),
                    ])
                    ->columnSpan(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label(' ')
                    ->circular()
                    ->getStateUsing(fn (User $record) => $record->getFilamentAvatarUrl() ?? filament()->getUserAvatarUrl($record)),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('inspirecms::inspirecms.name'))
                    ->weight(FontWeight::Bold)
                    ->sortable()->width('1%'),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('inspirecms::inspirecms.email')),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label(__('inspirecms::inspirecms.roles')),
                Tables\Columns\TextColumn::make('last_logged_in_at')
                    ->label(__('inspirecms::inspirecms.last_logged_in_at')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
            ])
            ->filters([

                // Keywords search
                // Tables\Filters\TrashedFilter
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('{record}'),
        ];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getUserModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.user');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['roles']);
    }

    //region Form field(s)/component(s)
    /**
     * @return Forms\Components\Field|Forms\Components\Component
     */
    protected static function getNameFormComponent()
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('inspirecms::inspirecms.name'))
            ->required()
            ->maxLength(255);
    }

    /**
     * @return Forms\Components\Field|Forms\Components\Component
     */
    protected static function getEmailFormComponent()
    {
        return Forms\Components\TextInput::make('email')
            ->label(__('inspirecms::inspirecms.email'))
            ->email()
            ->required()
            ->maxLength(255)
            ->autofocus()
            ->unique(table: static::getModel(), column: 'email', ignoreRecord: true);
    }

    /**
     * @return Forms\Components\Field|Forms\Components\Component
     */
    protected static function getPasswordFormComponent()
    {
        return Forms\Components\TextInput::make('password')
            ->label(__('inspirecms::inspirecms.password'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->rule(Password::default())
            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
            ->same('passwordConfirmation')
            ->validationAttribute(__('inspirecms::pages/auth/install.form.password.validation_attribute'));
    }

    /**
     * @return Forms\Components\Field|Forms\Components\Component
     */
    protected static function getPasswordConfirmationFormComponent()
    {
        return Forms\Components\TextInput::make('passwordConfirmation')
            ->label(__('inspirecms::inspirecms.password_confirmation'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->dehydrated(false);
    }

    /**
     * @return Forms\Components\Field|Forms\Components\Component
     */
    protected static function getRolesFormComponent()
    {
        return UserRolePicker::make('roles')
            ->label(__('inspirecms::inspirecms.role'))
            ->required()
            ->columnSpanFull();
    }
    //endregion Form field(s)/component(s)
}

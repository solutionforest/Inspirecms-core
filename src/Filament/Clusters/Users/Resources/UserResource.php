<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Users\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Password;
use SolutionForest\InspireCms\Filament\Clusters\Users;
use SolutionForest\InspireCms\Filament\Clusters\Users\Resources\UserResource\Pages;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Filament\Forms\Components\UserRolePicker;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\User;

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
                    ->label(__('inspirecms::resources/user.name.label'))
                    ->weight(FontWeight::Bold)
                    ->sortable()->width('1%'),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('inspirecms::resources/user.email.label')),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label(__('inspirecms::resources/user.roles.label')),
                Tables\Columns\TextColumn::make('last_logged_in_at')
                    ->label(__('inspirecms::resources/user.last_logged_in_at.label')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
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

    // region Global search
    public static function getGloballySearchableAttributes(): array
    {
        return ['id', 'email'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return new HtmlString(<<<Html
            <p>{$record->email}</p>
            <p>{$record->name}</p>
        Html);
    }
    // endregion Global search

    // region Form field(s)/component(s)
    /**
     * @return Forms\Components\Field|Forms\Components\Component
     */
    protected static function getNameFormComponent()
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('inspirecms::resources/user.name.label'))
            ->required()
            ->maxLength(255);
    }

    /**
     * @return Forms\Components\Field|Forms\Components\Component
     */
    protected static function getEmailFormComponent()
    {
        return Forms\Components\TextInput::make('email')
            ->label(__('inspirecms::resources/user.email.label'))
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
            ->label(__('inspirecms::resources/user.password.label'))
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
            ->label(__('inspirecms::resources/user.password_confirmation.label'))
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
            ->label(__('inspirecms::resources/user.roles.label'))
            ->required()
            ->columnSpanFull();
    }
    // endregion Form field(s)/component(s)
}

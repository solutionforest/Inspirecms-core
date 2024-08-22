<?php

namespace SolutionForest\InspireCms\Filament\Resources\Settings;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use SolutionForest\InspireCms\Filament\Forms\Components\BelongsToParentSelect;
use SolutionForest\InspireCms\Filament\Forms\Components\DocumentFieldGroup;
use SolutionForest\InspireCms\Filament\Resources\Settings\DocumentTypeResource\Pages;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class DocumentTypeResource extends Resource
{
    protected static ?int $navigationSort = -10;

    protected static ?string $navigationIcon = null;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Section::make()
                    ->columns(1)
                    ->schema([
                        static::getTitleFormComponent()->inlineLabel()->columnSpanFull(),
                        static::getFieldGroupFormComponent(),
                    ]),
            ]);
    }

    public static function detailInfoForm(Form $form): Form
    {

        return $form
            ->columns(['default' => 1, 'sm' => '2'])
            ->schema([
                Forms\Components\Group::make([
                    static::getParentIdFormComponent(),
                    static::getCanUseAtRootFormComponent(),
                ])->columnSpan(['default' => 'full', 'lg' => 'full', 'sm' => 1]),
                static::getTimestampsGroupedFormComponent()
                    ->columnSpan(['default' => 1, 'lg' => 'full', 'md' => 1]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('inspirecms::inspirecms.id'))
                    ->width('1%')->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('inspirecms::inspirecms.title'))
                    ->sortable(),
                Tables\Columns\IconColumn::make('can_use_at_root')
                    ->label(__('inspirecms::inspirecms.can_use_at_root'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->iconButton(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocumentTypes::route('/'),
            'create' => Pages\CreateDocumentType::route('/create'),
            'edit' => Pages\EditDocumentType::route('/{record}/edit'),
        ];
    }

    public static function getModel(): string
    {
        return InspireCmsConfig::getDocumentTypeModelClass();
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms::inspirecms.document_type');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('inspirecms::inspirecms.setting');
    }

    //region Form field(s)/component(s)

    protected static function getTitleFormComponent(): Forms\Components\Component
    {
        return Forms\Components\TextInput::make('title')
            ->label(__('inspirecms::inspirecms.title'))
            ->required();
    }

    protected static function getFieldGroupFormComponent(): Forms\Components\Component
    {
        return DocumentFieldGroup::make()->inlineLabel();
    }

    protected static function getParentIdFormComponent(): Forms\Components\Component
    {
        return BelongsToParentSelect::make('parent_id')
            ->label(__('inspirecms::inspirecms.parent_xxx', [
                'name' => strtolower(__('inspirecms::inspirecms.document_type')),
            ]))
            ->nestableParentRelationship('parent', 'title', ignoreRecord: true)
            ->searchable(['title'])
            ->preload()
            ->placeholder('(' . strtolower(__('inspirecms::inspirecms.no_parent') . ')'))
            ->hintIcon(
                'heroicon-o-information-circle',
                __('inspirecms::inspirecms.hints.parent_document_type_field_groups')
            )
            ->live()
            ->afterStateUpdated(function ($livewire) {
                $livewire->form
                    ->getComponent('documentFieldGroup')
                    ?->getChildComponentContainer()
                    ?->getComponent('parentFieldGroupsPreview')
                    ?->fill();
            });
    }

    protected static function getCanUseAtRootFormComponent(): Forms\Components\Component
    {
        return Forms\Components\Toggle::make('can_use_at_root')
            ->label(__('inspirecms::inspirecms.can_use_at_root'))
            ->inlineLabel()
            ->default(false);
    }

    protected static function getTimestampsGroupedFormComponent(): Forms\Components\Component
    {
        return Forms\Components\Group::make([
            Forms\Components\Placeholder::make('created_at')
                ->content(fn ($record) => $record->created_at?->shortRelativeToNowDiffForHumans())
                ->label(__('inspirecms::inspirecms.created_at'))
                ->inlineLabel(),
            Forms\Components\Placeholder::make('updated_at')
                ->content(fn ($record) => $record->updated_at?->shortRelativeToNowDiffForHumans())
                ->label(__('inspirecms::inspirecms.last_updated_at'))
                ->inlineLabel(),
        ])->visible(fn ($operation) => $operation == 'edit')
            ->columns(['default' => 1]);
    }

    //endregion Form field(s)/component(s)
}

<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Filament\Resources\Contents\PageResource\Pages;
use SolutionForest\InspireCms\Models\CmsPage;

class PageResource extends Resource
{
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label(__('inspirecms-core::inspirecms-core.title'))
                    ->validationAttribute(Str::lower(__('inspirecms-core::inspirecms-core.title')))
                    ->required(),
            ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')->width('1%')->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('inspirecms-core::inspirecms-core.title'))
                    ->sortable(),
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }

    public static function getModel(): string
    {
        return config('inspirecms-core.models.page.fqcn', CmsPage::class);
    }

    public static function getModelLabel(): string
    {
        return __('inspirecms-core::inspirecms-core.page');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('inspirecms-core::inspirecms-core.content');
    }
}

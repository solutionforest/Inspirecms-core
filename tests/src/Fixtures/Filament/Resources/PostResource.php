<?php

namespace SolutionForest\InspireCms\Tests\Fixtures\Filament\Resources;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionResourceTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionResource;
use SolutionForest\InspireCms\Tests\Fixtures\Filament\Resources\PostResource\Pages\ListPosts;
use SolutionForest\InspireCms\Tests\Models\Post;

class PostResource extends Resource implements ClusterSectionResource
{
    use ClusterSectionResourceTrait;

    protected static ?string $model = Post::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = Settings::class;

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            // ->query(Post::query())
            // ->recordKey('id')
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
            ]);
    }
}

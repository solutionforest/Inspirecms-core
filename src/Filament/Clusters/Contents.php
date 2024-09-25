<?php

namespace SolutionForest\InspireCms\Filament\Clusters;

use Filament\Actions\CreateAction;
use Filament\Clusters\Cluster;
use Filament\Resources\Components\Tab;
use Filament\Resources\Concerns\HasTabs;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\DataTypes\Manifest\ContentStatusOption;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Concerns\ConfigureContentsSubNavigation;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Pages\CreateRootContent;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\PageResource;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSection;

class Contents extends Cluster implements ClusterSection, HasTable
{
    use ClusterSectionTrait;
    use ConfigureContentsSubNavigation;
    use InteractsWithTable;
    use HasTabs;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?int $navigationSort = 1;

    /**
     * @var view-string
     */
    protected static string $view = 'inspirecms::filament.clusters.contents.index';

    public function mount(): void
    {
        // Disable redirecting to the first available sub-navigation item

        $this->activeTab ??= 'all';
    }

    protected function authorizeAccess(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    protected function queryString()
    {
        return [
            'activeTab',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->url(function () {
                    $page = config('inspirecms.pages.create_root_content', CreateRootContent::class);
                    return $page::getUrl();
                })
                ->authorize('create')
                ->model($this->getModel()),
        ];
    }

    public function getTabs(): array
    {
        return inspirecms_content_statuses()->all()
            ->mapWithKeys(
                fn (ContentStatusOption $option) => [
                    $option->getName() => Tab::make()
                        ->icon($option->getIcon())
                        ->label($option->getLabel())
                        ->badge($option->getName() != 'unpublish' ? $this->getTableQuery()->where('status', $option->getValue())->isPublished()->count() : null)
                        ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $option->getValue())),
                ]
            )
            ->prepend(Tab::make(), 'all')
            ->toArray();
    }

    public function table(Table $table): Table
    {
        $resource = static::getContentResource();

        return $resource::table($table)
            ->query($this->getTableQuery())
            ->recordUrl(fn (Model $record) => $resource::getUrl('index', ['parent' => $record]))
            ->actions([
                Tables\Actions\ViewAction::make()->iconButton(),
            ]);
    }
    
    protected function getTableQuery(): ?Builder
    {
        return static::getContentResource()::getEloquentQuery()
            ->isRootLevel();
    }

    protected function getModel(): string
    {
        return static::getContentResource()::getModel();
    }

    public function getTitle(): string | Htmlable
    {
        return __('inspirecms::inspirecms.contents');
    }

    public static function getNavigationLabel(): string
    {
        return __('inspirecms::inspirecms.contents');
    }

    public static function getClusterBreadcrumb(): ?string
    {
        return __('inspirecms::inspirecms.contents');
    }

    public static function getClusteredComponents(): array
    {
        return array_filter([
            config('inspirecms.resources.page'),
        ]);
    }

    protected static function getContentResource(): string
    {
        return  config('inspirecms.resources.page', PageResource::class);
    }

    protected function configureTableAction(Action $action): void
    {
        match (true) {
            $action instanceof Tables\Actions\ViewAction => $this->configureViewAction($action),
            default => null,
        };
    }

    protected function configureTableBulkAction(BulkAction $action): void
    {
        match (true) {
            $action instanceof Tables\Actions\DeleteBulkAction => $this->configureDeleteBulkAction($action),
            default => null,
        };
    }

    protected function configureViewAction(Tables\Actions\ViewAction $action): void
    {
        $resource = static::getContentResource();

        $action
            ->authorize(fn (Model $record): bool => $resource::canView($record))
            ->url(fn (Model $record) => $resource::getUrl('index', ['parent' => $record]));
    }

    protected function configureDeleteBulkAction(Tables\Actions\DeleteBulkAction $action): void
    {
        $resource = static::getContentResource();

        $action
            ->authorize($resource::canDeleteAny())
            ->successRedirectUrl(fn () => $this->getUrl());
    }
}

<?php

namespace SolutionForest\InspireCms\Filament\Pages;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Facades\PermissionManifest;
use SolutionForest\InspireCms\Factories\ContentSegmentFactory;
use SolutionForest\InspireCms\Factories\SitemapGeneratorFactory;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionPageTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionPage;
use SolutionForest\InspireCms\Filament\Contracts\GuardPage;
use SolutionForest\InspireCms\Filament\Widgets\CmsVersionInfo;
use SolutionForest\InspireCms\Helpers\AuthHelper;
use SolutionForest\InspireCms\Helpers\PermissionHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Support\Helpers\KeyHelper;
use SolutionForest\InspireCms\Support\Models\Contracts\NestableTree;
use Throwable;

// todo: need redo the layout
class Health extends Page implements ClusterSectionPage, GuardPage, HasActions, HasForms
{
    use ClusterSectionPageTrait;
    use InteractsWithActions;
    use InteractsWithForms;

    public string $view = 'inspirecms::filament.pages.health';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $cluster = Settings::class;

    public static function getPermissionName(): string
    {
        return 'pages_view-health';
    }

    public static function getPermissionDisplayName(): string
    {
        return __('inspirecms::pages/health.title');
    }

    public function getTitle(): string | Htmlable
    {
        return static::getNavigationLabel();
    }

    public static function getNavigationLabel(): string
    {
        return __('inspirecms::pages/health.title');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CmsVersionInfo::class,
        ];
    }

    public function getStatusInfo(): array
    {
        $permissions = $this->getPermissionsStatusData();
        $sitemap = $this->getSiteMapStatusData();
        $contentHierarchy = $this->getContentHierarchyStatusData();

        return [
            'permissions' => [
                'title' => __('inspirecms::pages/health.permissions.label'),
                ...$permissions,
                'action' => 'fix',
            ],
            'sitemap' => [
                'title' => __('inspirecms::pages/health.sitemap.label'),
                ...$sitemap,
                'action' => 'fix',
            ],
            'content_hierarchy' => [
                // 'title' => __('inspirecms::pages/health.content_hierarchy.label'),
                'title' => 'Content Hierarchy',
                ...$contentHierarchy,
                'action' => 'fix',
            ],
        ];
    }

    public function fixAction(): Action
    {
        return Action::make('fix')
            ->label(__('inspirecms::buttons.fix.label'))
            ->outlined()
            ->size('sm')
            ->action(function (array $arguments) {

                $needRefresh = false;

                switch ($arguments['action']) {
                    case 'permissions':
                        $needRefresh = $this->fixPermissions();

                        break;

                    case 'sitemap':
                        $needRefresh = $this->fixSiteMap();

                        break;

                    case 'content_hierarchy':
                        $needRefresh = $this->fixContentHierarchy();

                        break;

                }

                if ($needRefresh) {
                    $this->dispatch('$refresh');
                }
            });
    }

    protected function getPermissionsStatusData(): array
    {
        $permissions = $this->getAllPermissions();

        $missing = $this->getMissingPermissions($permissions);

        return [
            'status' => $this->formateStatusData(count($permissions), count($missing), count($missing) == 0),
            'data' => $this->formateStatusContent([
                'Missing permissions' => array_values($missing),
            ]),
        ];
    }

    private function getMissingPermissions(array $permissions = []): array
    {
        // check permissions exist
        $permissionModel = InspireCmsConfig::getPermissionModelClass();
        $existingPermissions = $permissionModel::whereIn('name', $permissions)->whereGuardName(AuthHelper::guardName())->pluck('name')->toArray();

        return array_diff($permissions, $existingPermissions);
    }

    protected function getSiteMapStatusData(): array
    {
        // Determin the sitemap is generated or not
        $fullFilePath = SitemapGeneratorFactory::create()?->getFilePath();

        return [
            'status' => $this->formateStatusData(1, file_exists($fullFilePath) ? 0 : 1, file_exists($fullFilePath)),
            'data' => $this->formateStatusContent([
                'Missing sitemap file',
            ]),
        ];
    }

    protected function getContentHierarchyStatusData(): array
    {
        $records = InspireCmsConfig::getNestableTreeModelClass()::scoped([
            'nestable_type' => app(InspireCmsConfig::getContentModelClass())->getMorphClass(),
        ])->with([
            'nestable.path',
            'nestable.ancestorsAndSelf',
        ])->get();

        $segmentProvider = ContentSegmentFactory::create();

        $data = $records
            ->reject(fn (NestableTree | Model $record) => is_null($record->nestable))
            ->map(fn (NestableTree | Model $record) => [
                'id' => $record->nestable->getKey(),
                'slug' => $record->nestable->slug,
                'parent_id_from_tree' => $record->parent?->nestable_id ?? KeyHelper::generateMinUuid(),
                'parent_id_from_content' => $record->nestable->getParentId(),
                'current_path' => $record->nestable->path?->value,
                'expected_path' => $segmentProvider->getPath($record->nestable),
            ])
            ->keyBy('id')
            ->all();

        $valids = collect($data)->filter(function ($item) {
            return $item['current_path'] === $item['expected_path'] &&
                $item['parent_id_from_tree'] === $item['parent_id_from_content'];
        })->all();

        $invalids = collect($data)
            ->where(fn ($item, $key) => array_key_exists($key, $valids) === false)
            ->all();

        return [
            'status' => $this->formateStatusData(count($data), count($invalids), count($valids)),
            'data' => $this->formateStatusContent(
                collect($invalids)
                    ->mapWithKeys(function ($item) {
                        return [
                            'Content ID: ' . $item['id'] => [
                                'Current path: ' . $item['current_path'],
                                'Expected path: ' . $item['expected_path'],
                            ],
                        ];
                    })
                    ->all()
            ),
        ];
    }

    protected function fixPermissions(): bool
    {
        $missing = $this->getMissingPermissions($this->getAllPermissions());

        if (empty($missing)) {
            return false;
        }

        PermissionHelper::setupPermissions();

        return true;
    }

    protected function fixSiteMap(): bool
    {
        try {
            // call the sitemap generator
            $generator = SitemapGeneratorFactory::create();

            $generator->generateSitemapFile();
        } catch (Throwable $th) {

            Notification::make()
                ->danger()
                ->title($th->getMessage())
                ->send();

            return false;
        }

        return true;
    }

    protected function fixContentHierarchy(): bool
    {
        $records = InspireCmsConfig::getContentModelClass()::with(['path', 'ancestorsAndSelf'])->get();
        $segmentProvider = ContentSegmentFactory::create();

        $records->each(function (Content | Model $record) use ($segmentProvider) {
            $expectedPath = $segmentProvider->getPath($record);

            if ($record->path?->value !== $expectedPath) {
                $record->path?->update([
                    'value' => $expectedPath,
                ]);
            }
        });

        return true;
    }

    protected function formateStatusData($total, $invalid, $valid): array
    {
        return [
            'total' => $total,
            'invalid' => $invalid,
            'isHealthy' => $valid,
        ];
    }

    protected function formateStatusContent(array $invalidItems): array
    {
        return [
            'invalidMessage' => $invalidItems,
        ];
    }

    private function getAllPermissions(): array
    {
        return PermissionManifest::permissions()->unique()->sort()->values()->all();
    }
}

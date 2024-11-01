<?php

namespace SolutionForest\InspireCms\Base\Filament\Actions\Concerns;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentPicker;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Support\InspireCmsConfig;
use SolutionForest\InspireCms\Support\TreeNodes\Actions\Action as TreeNodeAction;

trait LinkContentToParentActionTrait
{
    protected Closure | string | int | null $rootLevelKey = null;

    protected Closure | string $parentIdColumnName = 'parent_id';

    public function rootLevelKey(Closure | string | int $rootLevelKey): static
    {
        $this->rootLevelKey = $rootLevelKey;

        return $this;
    }

    public function getRootLevelKey(): string | int
    {
        return $this->evaluate($this->rootLevelKey);
    }

    public function parentIdColumnName(Closure | string $parentIdColumnName): static
    {
        $this->parentIdColumnName = $parentIdColumnName;

        return $this;
    }

    public function getParentIdColumnName(): string
    {
        return (string) $this->evaluate($this->parentIdColumnName);
    }

    public static function getDefaultName(): ?string
    {
        return 'link_content_to_parent';
    }

    public static function getPermissionName(): string
    {
        return 'action_link_content_to_parent';
    }

    public static function getPermissionDisplayName(): string
    {
        return __('inspirecms::actions.link_to_parent.permission_display_name');
    }

    protected function setUpAction(): void
    {
        $this->label(__('inspirecms::actions.link_to_parent.label'));

        $this->successRedirectUrl(function () {
            $resource = InspireCmsConfig::get('filament.resources.page', PageResource::class);

            return FilamentResourceHelper::attemptToGetUrl($resource, 'index', [], false);
        });

        $this->successNotificationTitle(__('inspirecms::actions.link_to_parent.notifications.success.title'));

        $this->groupedIcon('heroicon-o-link');

        $this->model(InspireCmsConfig::getContentModelClass());

        $this->form(
            fn (Form $form, Model & Content $record) => $form
                ->schema([
                    Toggle::make('as_root')
                        ->label(__('inspirecms::inspirecms.as_root'))
                        ->live(),
                    ContentPicker::make('parent')
                        ->label(__('inspirecms::resources/content.parent.label'))
                        ->exceptRecord(fn () => [$record, $record->getParentId()])
                        ->maxItems(1)
                        ->minItems(1)
                        ->perPage(5)
                        ->visible(fn ($get) => $get('as_root') === false),
                ])
        );

        $this->action(function (array $data, Model & Content $record, Action|TreeNodeAction $action) {
            if (! $record) {
                return;
            }

            if (isset($data['as_root']) && $data['as_root'] === true) {

                $record->asRoot();

            } elseif (isset($data['parent'])) {

                $record->setParentNode($data['parent'][0]);
            }

            $action->success();
        });
    }
}

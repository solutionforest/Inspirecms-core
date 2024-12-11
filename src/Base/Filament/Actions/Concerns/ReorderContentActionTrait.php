<?php

namespace SolutionForest\InspireCms\Base\Filament\Actions\Concerns;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Support\Models\Contracts\NestableTree;
use SolutionForest\InspireCms\Support\TreeNodes\Actions\Action as TreeNodeAction;

trait ReorderContentActionTrait
{
    protected Closure | string | int | null $nodeParentId = null;

    public static function getDefaultName(): ?string
    {
        return 'reorder_content';
    }

    public static function getPermissionName(): string
    {
        return 'action_reorder_content';
    }

    public static function getPermissionDisplayName(): string
    {
        return __('inspirecms::resources/content.actions.reorder_content.permission_display_name');
    }

    public function nodeParentId(Closure | string | int $nodeParentId): static
    {
        $this->nodeParentId = $nodeParentId;

        return $this;
    }

    public function getNodeParentId(): string | int | null
    {
        return $this->evaluate($this->nodeParentId);
    }

    protected function setUpAction(): void
    {
        $this->label(__('inspirecms::resources/content.actions.reorder_content.label'));

        $this->successNotificationTitle(__('inspirecms::resources/content.actions.reorder_content.notification.success.title'));

        $this->groupedIcon('heroicon-o-arrows-up-down');

        $contentModel = InspireCmsConfig::getContentModelClass();
        $nestableTreeModel = InspireCmsConfig::getNestableTreeModelClass();

        $this->slideOver();

        $this->fillForm(fn ($action) => [
            'contents' => $contentModel::query()
                ->whereAncesterOfTree($action->getNodeParentId())
                ->sortedByTree()
                ->get()
                ->mapWithKeys(fn ($content) => [
                    $content->getKey() => [
                        'id' => $content->getKey(),
                        'title' => $content->title,
                        'slug' => $content->slug,
                    ],
                ])
                ->all(),
        ]);

        $this->form([
            Repeater::make('contents')
                ->hiddenLabel()
                ->addable(false)
                ->deletable(false)
                ->orderable()
                ->columns(2)
                ->schema([
                    TextInput::make('id')
                        ->hidden()
                        ->dehydratedWhenHidden(),
                    TextInput::make('title')
                        ->label(__('inspirecms::resources/content.title.label'))
                        ->inlineLabel()
                        ->disabled(),
                    TextInput::make('slug')
                        ->label(__('inspirecms::resources/content.slug.label'))
                        ->inlineLabel()
                        ->disabled(),
                ]),
        ]);

        $this->action(function (array $data, Action | TreeNodeAction $action) use ($contentModel, $nestableTreeModel) {

            if (! in_array(NestableTree::class, class_implements($nestableTreeModel))) {

                Notification::make()
                    ->title(__('inspirecms::resources/content.actions.reorder_content.notification.invalid_model.title'))
                    ->danger()
                    ->send();

                $action->failure();

                return;
            }

            $sortedKeys = collect($data['contents'])->pluck('id')->toArray();

            try {

                $nestableTreeModel::setNewOrderForNestable($this->getNodeParentId(), $sortedKeys, $contentModel);

                $action->success();

            } catch (\Throwable $th) {

                Notification::make()
                    ->title(__('inspirecms::resources/content.actions.reorder_content.notification.error.title'))
                    ->body($th->getMessage())
                    ->danger()
                    ->send();

                $action->failure();
            }

        });
    }
}

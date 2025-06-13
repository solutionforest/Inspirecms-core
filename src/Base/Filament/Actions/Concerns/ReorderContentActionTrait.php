<?php

namespace SolutionForest\InspireCms\Base\Filament\Actions\Concerns;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Support\Models\Contracts\NestableTree;
use SolutionForest\InspireCms\Support\TreeNode\Actions\Action as TreeNodeAction;

trait ReorderContentActionTrait
{
    protected Closure | string | int | null $nodeParentId = null;

    public static function getDefaultName(): ?string
    {
        return 'reorderContentChildren';
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
        $this->label(__('inspirecms::buttons.reorder_children.label'));

        $this->successNotificationTitle(__('inspirecms::buttons.reorder_children.messages.success.title'));

        $this->groupedIcon(FilamentIcon::resolve('inspirecms::sort'));

        /**
         * @var class-string<\SolutionForest\InspireCms\Models\Contracts\Content & Model> $contentModel
         */
        $contentModel = InspireCmsConfig::getContentModelClass();
        /**
         * @var class-string<\SolutionForest\InspireCms\Support\Models\Contracts\NestableTree & Model> $nestableTreeModel
         */
        $nestableTreeModel = InspireCmsConfig::getNestableTreeModelClass();

        $this->slideOver();

        $this->authorize('reorderChildren');

        $this->hidden(function (?Model $record) {
            if ($record && ($record->isLocked() || $record->trashed())) {
                return true;
            }

            return false;
        });

        $this->model($contentModel);

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
                ->itemLabel(fn (array $state): ?string => $state['title'] ?? $state['slug'] ?? null)
                ->collapsed()
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
                    ->title(__('inspirecms::buttons.reorder_children.messages.invalid_model.title'))
                    ->danger()
                    ->send();

                $action->failure();

                return;
            }

            $sortedKeys = Arr::pluck($data['contents'] ?? [], 'id') ?? [];

            try {

                $nestableTreeModel::setNewOrderForNestable($this->getNodeParentId(), $sortedKeys, $contentModel);

                $action->success();

            } catch (\Throwable $th) {

                Notification::make()
                    ->title(__('inspirecms::messages.something_went_wrong'))
                    ->body($th->getMessage())
                    ->danger()
                    ->send();

                $action->failure();
            }

        });
    }
}

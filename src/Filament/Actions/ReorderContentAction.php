<?php

namespace SolutionForest\InspireCms\Filament\Actions;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use SolutionForest\InspireCms\Support\InspireCmsConfig;
use SolutionForest\InspireCms\Support\Models\Contracts\NestableTree;

class ReorderContentAction extends Action
{
    protected Closure | string | int | null $nodeParentId = null;

    public static function getDefaultName(): ?string
    {
        return 'reorder_content';
    }

    protected function setUp(): void
    {
        // todo: permission check
        parent::setUp();

        $this->label(__('inspirecms::actions.reorder_content.label'));

        $this->successNotificationTitle(__('inspirecms::actions.reorder_content.notifications.success.title'));

        $this->groupedIcon('heroicon-o-arrows-up-down');

        $contentModel = InspireCmsConfig::getContentModelClass();
        $nestableTreeModel = InspireCmsConfig::getNestableTreeModelClass();

        $this->fillForm(fn (ReorderContentAction $action) => [
            'contents' => $contentModel::query()
                ->whereAncesterOfTree($action->getNodeParentId())
                ->sortedByTree()
                ->get()
                ->mapWithKeys(fn ($content) => [ 
                    $content->getKey() => [
                        'id' => $content->getKey(),
                        'title' => $content->title,
                        'slug' => $content->slug,
                    ]
                ])
                ->all()
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

        $this->action(function (array $data, Action $action) use ($contentModel, $nestableTreeModel) {

            if (! in_array(NestableTree::class, class_implements($nestableTreeModel))) {

                Notification::make()
                    ->title(__('inspirecms::actions.reorder_content.notifications.invalid_model.title'))
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
                    ->title(__('inspirecms::actions.reorder_content.notifications.error.title'))
                    ->body($th->getMessage())
                    ->danger()
                    ->send();

                $action->failure();
            }

        });
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
}

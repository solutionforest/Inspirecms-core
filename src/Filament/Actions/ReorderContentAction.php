<?php

namespace SolutionForest\InspireCms\Filament\Actions;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Support\Models\Contracts\NestableTree;

class ReorderContentAction extends Action
{
    protected Closure | string | int | null $nodeParentId = null;

    public static function getDefaultName(): ?string
    {
        return 'reorderContentChildren';
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->label(__('inspirecms::buttons.reorder_children.label'));

        $this->successNotificationTitle(__('inspirecms::buttons.reorder_children.messages.success.title'));

        $this->groupedIcon(FilamentIcon::resolve('inspirecms::sort'));

        /**
         * @var class-string<Content & Model> $contentModel
         */
        $contentModel = InspireCmsConfig::getContentModelClass();
        /**
         * @var class-string<NestableTree & Model> $nestableTreeModel
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

        $this->fillForm(function (self $action) use ($contentModel) {
            
            $query = $contentModel::query()
                ->with(['documentType']);

            $records = $query
                ->whereAncesterOfTree($action->getNodeParentId())
                ->sortedByTree()
                ->get()
                ->map(fn (Model $record) => [
                    ...$record->toArray(),
                    'title' => $record->title,
                    'icon' => $record->documentType?->icon,
                ]);

            return [
                'contents' => $records,
            ];
        });

        $this->schema([
            Repeater::make('contents')
                ->hiddenLabel()
                ->addable(false)
                ->deletable(false)
                ->orderable()
                ->itemLabel(fn (array $state): ?string => $state['title'] ?? $state['slug'] ?? null)
                ->table([
                    // TableColumn::make(''),
                    TableColumn::make(__('inspirecms::resources/content.title.label')),
                    TableColumn::make(__('inspirecms::resources/content.slug.label')),
                ])
                ->schema([
                    TextInput::make('title')
                        ->readOnly()
                        ->prefixIcon(function ($get) {
                            $item = $get('./');
                            if (! is_array($item)) {
                                return null;
                            }

                            return data_get($item, 'document_type.icon');
                        })
                        ,
                    TextInput::make('slug')
                        ->readOnly(),
                ]),
        ]);

        $this->action(function (array $data, Action $action) use ($contentModel, $nestableTreeModel) {

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

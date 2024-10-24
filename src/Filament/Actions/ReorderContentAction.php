<?php

namespace SolutionForest\InspireCms\Filament\Actions;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Support\InspireCmsConfig;
use SolutionForest\InspireCms\Support\Models\Contracts\NestableTree;

class ReorderContentAction extends Action
{
    protected Closure | string | int | null $parentId = null;

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

        $this->model(InspireCmsConfig::getNestableTreeModelClass());

        $this->groupedIcon('heroicon-o-arrows-up-down');

        $contentModel = InspireCmsConfig::getContentModelClass();

        $this->form(function (Form $form, string $model, ?Model $record) use ($contentModel) {

            $contents = $model::query()
                ->whereHasMorph('nestable', [$contentModel])
                ->parent($this->getParentId())
                ->get()
                ->map(fn ($tree) => $tree->nestable)
                ->map(fn ($content) => [
                    'id' => $content->getKey(),
                    'title' => $content->title,
                    'slug' => $content->slug,
                ])
                ->all();

            return $form
                ->schema([
                    Repeater::make('contents')
                        ->hiddenLabel()
                        ->addable(false)
                        ->deletable(false)
                        ->orderable()
                        ->afterStateHydrated(function (Repeater $component) use ($contents) {
                            $state = collect($contents)
                                ->mapWithKeys(fn ($data) => [$component->generateUuid() => $data])
                                ->all();
                            $component->state($state);
                        })
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
        });

        $this->action(function (array $data, string $model, Action $action) use ($contentModel) {

            if (! in_array(NestableTree::class, class_implements($model))) {

                Notification::make()
                    ->title(__('inspirecms::actions.reorder_content.notifications.invalid_model.title'))
                    ->danger()
                    ->send();

                $action->failure();

                return;
            }

            $sortedKeys = collect($data['contents'])->pluck('id')->toArray();

            try {

                $morphableType = app($contentModel)->getMorphClass();

                $model::setNewOrderForNestable($sortedKeys, $morphableType);

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

    public function parentId(Closure | string | int $parentId): static
    {
        $this->parentId = $parentId;

        return $this;
    }

    public function getParentId(): string | int | null
    {
        return $this->evaluate($this->parentId);
    }
}

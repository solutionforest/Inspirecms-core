<?php

namespace SolutionForest\InspireCms\Filament\TreeNode\Actions;

use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Forms\Components\PaginationCheckboxList;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Support\TreeNodes\Actions\Action;

class MoveContentAction extends Action
{
    protected bool $moveUnderRoot = false;

    public static function getDefaultName(): ?string
    {
        return 'move_content';
    }

    protected function setUp(): void
    {
        // todo: add guard
        // todo: add translation

        parent::setUp();

        $this->authorize('update');

        $this->label(fn () => $this->isMoveUnderRoot() ? 'Move under root' : 'Move under content');

        $this->modalHeading(fn () => $this->isMoveUnderRoot() ? 'Move under root' : 'Move under content');

        $this->model(InspireCmsConfig::getContentModelClass());

        $this->hidden(function (?Model $record): bool {
            if (! $record || ! $record instanceof Content) {
                return true;
            }

            return false;
        });

        $this->form(function (null | Model | Content $record, string $model) {
            if ($this->isMoveUnderRoot()) {
                return null;
            }
            if (! $record) {
                return [];
            }

            return [
                // TODO: fix nextpage
                PaginationCheckboxList::make('target')
                    ->hiddenLabel()
                    ->validationAttribute('target')
                    ->paginationOptions(fn () => $model::query()->whereKeyNot($record->getKey()))
                    ->tableColumns([
                        TextColumn::make('id')->label(__('inspirecms::inspirecms.id')),
                        TextColumn::make('title')->label(__('inspirecms::resources/content.title.label')),
                        TextColumn::make('slug')->label(__('inspirecms::resources/content.slug.label'))->badge(),
                    ])
                    ->maxItems(1),
            ];
        });

        $this->slideOver(fn () => ! $this->isMoveUnderRoot());

        $this->modalFooterActionsAlignment(Alignment::Right);

        $this->requiresConfirmation(fn () => $this->isMoveUnderRoot());

        $this->successNotificationTitle('Moved');

        $this->action(function (null | Model | Content $record, ?array $data, Action $action) {
            if (! $record || ! $record instanceof Content) {
                return;
            }

            if ($this->isMoveUnderRoot()) {

                $record->asRoot();
                $action->success();

            } elseif (! empty($data['target'])) {
                $target = $data['target'][0];
                if ($target != null && $target != $record->getParentId()) {
                    $record->setParentNode($target, true);
                    $action->success();
                }
            }
        });
    }

    public function moveUnderRoot(bool $moveUnderRoot = true): static
    {
        $this->moveUnderRoot = $moveUnderRoot;

        return $this;
    }

    public function isMoveUnderRoot(): bool
    {
        return $this->moveUnderRoot;
    }
}

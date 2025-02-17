<?php

namespace SolutionForest\InspireCms\Filament\TreeNode\Actions;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentTree;
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

        $this->label(fn () => $this->isMoveUnderRoot() ? 'Move under root' : 'Move under content');

        $this->modalHeading(fn () => $this->isMoveUnderRoot() ? 'Move under root' : 'Move under content');

        $this->model(InspireCmsConfig::getContentModelClass());

        $this->authorize('update');

        $this->hidden(function (?Model $record): bool {
            if (! $record || ! $record instanceof Content) {
                return true;
            }

            if ($record->isLocked()) {
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

            $restrictedDocumentTypeIds = InspireCmsConfig::getRejectedDocumentTypeModelClass()::query()
                ->where('rejected_document_type_id', $record->document_type_id)
                ->pluck('document_type_id')
                ->all();

            return [
                ContentTree::make('target')
                    ->hiddenLabel()
                    ->validationAttribute('target')
                    ->whereKeyNot($record->getKey())
                    ->whereNotIn(
                        'document_type_id',
                        $restrictedDocumentTypeIds,
                    )
                    ->maxItems(1)
                    ->minItems(1),
            ];
        });

        $this->slideOver(fn () => ! $this->isMoveUnderRoot());

        $this->requiresConfirmation(fn () => $this->isMoveUnderRoot());

        $this->successNotificationTitle('Moved');

        $this->action(function (null | Model | Content $record, ?array $data, Action $action) {
            if (! $record || ! $record instanceof Content) {
                return;
            }

            if ($this->isMoveUnderRoot()) {

                $record->asRoot();
                $action->success();

            } elseif (($target = $data['target'][0] ?? null) && $target != null && $target != $record->getParentId()) {
                $record->setParentNode($target, true);
                $action->success();
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

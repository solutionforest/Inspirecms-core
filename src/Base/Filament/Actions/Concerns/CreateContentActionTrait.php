<?php

namespace SolutionForest\InspireCms\Base\Filament\Actions\Concerns;

use Closure;
use Filament\Actions\Action;
use Filament\Tables\Actions\Action as TableAction;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

trait CreateContentActionTrait
{
    protected null | Closure | string | int $parentContentKey = null;

    protected ?Closure $documentTypeTitleUsing = null;

    public static function getDefaultName(): ?string
    {
        return 'create_content';
    }

    /**
     * Sets up the action based on the specified action type.
     *
     * @param  string  $actionType  The type of action to set up.
     */
    protected function setUpAction(string $actionType): void
    {
        if (blank($actionType) || ! class_exists($actionType) ||
            ! (
                is_a($actionType, Action::class, true) ||
                is_a($actionType, TableAction::class, true)
            )
        ) {
            throw new \InvalidArgumentException('The action type must be a valid Action or TableAction class.');
        }

        $contentResource = config('inspirecms.filament.resources.page', PageResource::class);

        $this->authorize('create', InspireCmsConfig::getContentModelClass());

        $this->label(__('inspirecms::actions.create_content.label'));

        $this->icon('heroicon-o-plus');

        $this->hidden(fn () => ! $contentResource::can('create'));

        $this->slideOver();

        $this->modal();

        $this->modalWidth('lg');

        $this->stickyModalHeader();

        $this->modalHeading(fn () => __('inspirecms::actions.create_content.label'));

        $this->modalContent(function () use ($actionType) {

            $documentTypes = InspireCmsConfig::getDocumentTypeModelClass()::query()
                ->isWebPage()
                ->get();

            return view('inspirecms::filament.actions.create-content', [
                'documentTypes' => $documentTypes,
                'getLabelUsing' => fn (?Model $record) => $this->getDocumentTypeTitleFor($record) ?? $record?->title,
                'actionType' => match ($actionType) {
                    Action::class => 'action',
                    TableAction::class => 'table-action',
                    default => null,
                },
            ]);
        });

        // use 'selectDocumentType' action
        $this->modalSubmitAction(false);

        // Needed. call from view
        $this->extraModalFooterActions([
            $actionType::make('selectDocumentType')
                // hide from frontend and keep it action
                ->extraAttributes([
                    'class' => 'hidden',
                ])
                ->cancelParentActions() // cancel parent actions if this action is cancelled
                ->action(function (array $arguments, Action | TableAction $action) use ($contentResource) {
                    if (! isset($arguments['documentTypeKey'])) {
                        $action->cancel();

                        return;
                    }
                    $url = FilamentResourceHelper::attemptToGetUrl(
                        $contentResource,
                        'create',
                        [
                            'documentType' => $arguments['documentTypeKey'],
                            'parent' => $this->getParentContentKey(),
                        ],
                        false
                    );
                    if (blank($url)) {
                        $action->cancel();

                        return;
                    }
                    $action->redirect($url);
                }),
        ]);
    }

    public function parentContentKey(Closure | string | int | null $parentContentKey): static
    {
        $this->parentContentKey = $parentContentKey;

        return $this;
    }

    public function getDocumentTypeTitleUsing(Closure $callback): static
    {
        $this->documentTypeTitleUsing = $callback;

        return $this;
    }

    public function getParentContentKey(): null | Closure | string | int
    {
        return $this->evaluate($this->parentContentKey);
    }

    public function getDocumentTypeTitleFor(?Model $record): ?string
    {
        return $this->evaluate($this->documentTypeTitleUsing, ['record' => $record]);
    }
}

<?php

namespace SolutionForest\InspireCms\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Base\Filament\Actions\Concerns\WithPagination;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;

class ContentHistoryAction extends Action
{
    use WithPagination;

    public static function getDefaultName(): ?string
    {
        return 'contentHistory';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setPerPage(5);
        $this->setPageOptions([5, 10, 20, 100, 'all']);

        $this->label(fn () => __('inspirecms::resources/content.actions.content_history.label'));

        $this->hidden(fn (null | Model | Content $record) => is_null($record));

        $this->authorize('viewHistory');

        $this->model(InspireCmsConfig::getContentModelClass());

        $this->slideOver();

        $this->modalContent(function (Model | Content $record, ContentHistoryAction $action, array $arguments) {

            $pageName = 'page';
            $page = $action->getPage($pageName) ?? 1;
            $perPage = $action->getPerPage();

            return view('inspirecms::filament.actions.content-history', [
                'page' => $page,
                'perPage' => $perPage,
                'paginator' => $record->contentVersions()
                    ->with(['publishLog', 'author'])
                    ->orderByDesc('created_at')
                    ->paginate(
                        perPage: $perPage === 'all' ? null : $perPage,
                        pageName: $pageName,
                        page: $page,
                    ),
                'action' => $action,
            ]);
        });

        $this->icon('heroicon-o-clock');

        $this->modalSubmitAction(false);
        $this->modalCancelAction(false);

        $this->modalWidth('6xl');

        $this->color('gray');

        $this->disabledForm();

        $this->registerModalActions([
            Action::make('toggleAvoidToClear')
                ->size('xs')
                ->hidden(function (?Model $record, $arguments) {
                    if ($record?->isLocked() || $record?->trashed()) {
                        return true;
                    }
                    
                    return ! isset($arguments['itemKey']);
                })
                ->label(fn ($arguments) => ($arguments['avoidToClear'] ?? false) ? 'Avoid to clean' : 'Wait to clean')
                ->color(fn ($arguments) => ($arguments['avoidToClear'] ?? false) ? 'gray' : 'danger')
                ->outlined()
                ->successNotificationTitle(fn ($arguments) => ($arguments['avoidToClear'] ?? false) ? 'Now avoiding to clean' : 'Now waiting to clean')
                ->successNotification(
                    fn (Notification $notification) => $notification
                        ->icon('heroicon-o-exclamation-triangle')
                        ->color('warning')
                        ->iconColor('warning')
                )
                ->action(function (array $arguments, Action $action, Model | Content $record) {
                    if (! isset($arguments['itemKey'])) {
                        return;
                    }

                    $contentVersion = $record->contentVersions()->find($arguments['itemKey']);
                    if (! $contentVersion) {
                        return;
                    }

                    $contentVersion->avoid_to_clean = ! $contentVersion->avoid_to_clean;
                    $contentVersion->save();

                    $action->success();
                }),
            ...$this->getPaginationActions(),
        ]);
    }
}

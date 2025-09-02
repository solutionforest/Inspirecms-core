<?php

namespace SolutionForest\InspireCms\Filament\Actions;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;

class ContentHistoryAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'contentHistory';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn () => __('inspirecms::buttons.content_history.label'));

        $this->hidden(fn (null | Model | Content $record) => is_null($record));

        $this->authorize('viewHistory');

        $this->model(InspireCmsConfig::getContentModelClass());

        $this->slideOver();

        $this
            ->modalContent(function ($livewire, Model | Content $record, ContentHistoryAction $action, array $arguments) {
                return view('inspirecms::filament.actions.content-history', [
                    'record' => $record,
                ]);
            })
            ->modalSubmitAction(false)
            ->modalCancelAction(false);

        $this->icon('heroicon-o-clock');

        $this->modalWidth('6xl');

        $this->color('gray');
    }
}

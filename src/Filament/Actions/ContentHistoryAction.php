<?php

namespace SolutionForest\InspireCms\Filament\Actions;

use Filament\Actions\Action;

class ContentHistoryAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'contentHistory';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn () => __('inspirecms::actions.content_history.label'));

        $this->hidden(fn ($record) => is_null($record));

        $this->slideOver();

        $this->modalContent(fn ($record) => view('inspirecms::filament.actions.content-history', [
            'record' => $record,
        ]));

        $this->icon('heroicon-o-clock');

        $this->modalSubmitAction(false);
        $this->modalCancelAction(false);

        $this->color('gray');

        $this->disabledForm();
    }
}

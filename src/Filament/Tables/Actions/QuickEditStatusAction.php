<?php

namespace SolutionForest\InspireCms\Filament\Tables\Actions;

use Filament\Support\Enums\Alignment;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Actions\Action;

class QuickEditStatusAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'quick_edit_status';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('inspirecms::inspirecms.actions.quick_status_edit.label'));

        $this->modalHeading(fn (): string => __('inspirecms::inspirecms.actions.quick_status_edit.modal.heading'));

        $this->modalSubmitActionLabel(__('inspirecms::inspirecms.actions.quick_status_edit.modal.actions.save.label'));

        $this->icon(FilamentIcon::resolve('actions::edit-action') ?? 'heroicon-m-pencil-square');
        
        $this->color('primary');

        $this->modalFooterActionsAlignment(Alignment::End);
    }
}

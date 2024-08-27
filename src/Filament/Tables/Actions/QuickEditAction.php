<?php

namespace SolutionForest\InspireCms\Filament\Tables\Actions;

use Filament\Tables\Actions\EditAction;

class QuickEditAction extends EditAction
{
    public static function getDefaultName(): ?string
    {
        return 'quick_edit';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('inspirecms::inspirecms.actions.quick_edit.label'));

        $this->modalHeading(fn (): string => __('inspirecms::inspirecms.actions.quick_edit.modal.heading', ['label' => $this->getRecordTitle()]));

        $this->modalSubmitActionLabel(__('inspirecms::inspirecms.actions.quick_edit.modal.actions.save.label'));

        $this->color('lime');
    }
}

<?php

namespace SolutionForest\InspireCms\Filament\Actions;

use Filament\Actions\Action;

class SaveAndPublishAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'saveAndPublish';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('inspirecms-core::inspirecms-core.actions.save_and_publish.label'));
    }
}

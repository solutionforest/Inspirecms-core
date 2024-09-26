<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages;

use Livewire\WithPagination;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseCreatePage;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\CanBePublish;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentPageTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\HasPublishForm;

abstract class BaseContentCreatePage extends BaseCreatePage implements HasPublishForm
{
    use CanBePublish;
    use ContentPageTrait;
    use WithPagination;

    protected function getFormActions(): array
    {
        return [
            $this->getPublishFormAction('create', $this->getModel()),
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }
}

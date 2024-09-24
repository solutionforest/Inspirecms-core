<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Contents\Resources\Pages;

use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseCreatePage;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Concerns\CanBePublish;
use SolutionForest\InspireCms\Filament\Clusters\Contents\Contracts\HasPublishForm;

abstract class BaseContentCreatePage extends BaseCreatePage implements HasPublishForm
{
    use CanBePublish;

    protected function getFormActions(): array
    {
        return [
            $this->getPublishFormAction('create', $this->getModel()),
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }
}

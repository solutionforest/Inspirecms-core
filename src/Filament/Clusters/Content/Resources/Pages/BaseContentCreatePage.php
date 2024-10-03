<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages;

use Livewire\WithPagination;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseCreatePage;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\CanBePublish;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentPageTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\CreateContentPageTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\ContentForm;
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\HasPublishForm;
use SolutionForest\InspireCms\Support\TreeNodes\Contracts\HasModelExplorer;

abstract class BaseContentCreatePage extends BaseCreatePage implements ContentForm, HasPublishForm, HasModelExplorer
{
    use CanBePublish;
    use WithPagination;
    use CreateContentPageTrait;
    use ContentPageTrait;

    protected static string $view = "inspirecms::filament.pages.content.create";

    protected function getFormActions(): array
    {
        return [
            $this->getPublishFormAction('create', $this->getModel()),
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();

        if ($resource::hasPage('view') && $resource::canView($this->getRecord())) {
            return $resource::getUrl('view', ['record' => $this->getRecord(), ...$this->getRedirectUrlParameters()]);
        }

        if ($resource::hasPage('edit') && $resource::canEdit($this->getRecord())) {
            return $resource::getUrl('edit', ['record' => $this->getRecord(), ...$this->getRedirectUrlParameters()]);
        }

        return $resource::getUrl('index');
    }
}

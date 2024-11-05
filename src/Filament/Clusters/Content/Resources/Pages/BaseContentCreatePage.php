<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages;

use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Livewire\WithPagination;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseCreatePage;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentFormTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentPageTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentPreviewEditorTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\CreateContentPageTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\ContentForm;
use SolutionForest\InspireCms\Support\TreeNodes\Contracts\HasModelExplorer;

abstract class BaseContentCreatePage extends BaseCreatePage implements ContentForm, HasModelExplorer
{
    use ContentFormTrait;
    use ContentPageTrait;
    use ContentPreviewEditorTrait;
    use CreateContentPageTrait;
    use CreateRecord\Concerns\Translatable {
        updatedActiveLocale as protected traitUpdatedActiveLocale;
    }
    use WithPagination;

    protected static string $view = 'inspirecms::filament.pages.content.create';

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            ...parent::getHeaderActions(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getPublishFormAction('create', $this->getModel()),
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getCreateFormAction(): Actions\Action
    {
        return parent::getCreateFormAction()
            ->label(__('inspirecms::actions.save_draft.label'))
            ->color('secondary');
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

    protected function getRedirectUrlParameters(): array
    {
        return [
            'activeRelationManager' => 0,
        ];
    }

    public function updatedActiveLocale(string $newActiveLocale): void
    {
        $this->updatedActiveLocaleForContent($newActiveLocale);
    }
}

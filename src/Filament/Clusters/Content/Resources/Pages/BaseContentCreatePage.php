<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Resources\Pages;

use Filament\Actions;
use Livewire\WithPagination;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseCreatePage;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentFormTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentPageTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\ContentPreviewEditorTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Concerns\CreateContentPageTrait;
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\ContentForm;

abstract class BaseContentCreatePage extends BaseCreatePage implements ContentForm
{
    use ContentFormTrait;
    use ContentPageTrait;
    use ContentPreviewEditorTrait;
    use CreateContentPageTrait;

    // Commented out to insteadof CreateRecord\Concerns\Translatable
    // use CreateRecord\Concerns\Translatable {
    //     ContentFormTrait::updatedActiveLocale insteadof CreateRecord\Concerns\Translatable;
    //     mountTranslatable as protected overrideMountTranslatableTrait;
    // }
    use CreatePage\Concerns\Translatable {
        ContentFormTrait::updatedActiveLocale insteadof CreatePage\Concerns\Translatable;
        mountTranslatable as protected overrideMountTranslatableTrait;
    }
    use WithPagination;

    protected static string $layout = 'inspirecms::components.layout.content-page';

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
            ->label(__('inspirecms::resources/content.actions.save_draft.label'))
            ->color('secondary');
    }

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();

        if ($resource::hasPage('edit') && $resource::canEdit($this->getRecord())) {
            return $resource::getUrl('edit', ['record' => $this->getRecord(), ...$this->getRedirectUrlParameters()]);
        }

        if ($resource::hasPage('view') && $resource::canView($this->getRecord())) {
            return $resource::getUrl('view', ['record' => $this->getRecord(), ...$this->getRedirectUrlParameters()]);
        }

        return $resource::getUrl('index', $this->getRedirectUrlParameters());
    }
}

<?php

namespace SolutionForest\InspireCms\Base\Filament\Resources\Pages;

use Filament\Actions\Action;
use Livewire\WithPagination;
use SolutionForest\InspireCms\Base\Filament\Concerns\ContentFormTrait;
use SolutionForest\InspireCms\Base\Filament\Concerns\ContentPageTrait;
use SolutionForest\InspireCms\Base\Filament\Concerns\ContentPreviewEditorTrait;
use SolutionForest\InspireCms\Base\Filament\Concerns\CreateContentPageTrait;
use SolutionForest\InspireCms\Base\Filament\Contracts\ContentForm;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\CreateContentRecord\Concerns\Translatable;

abstract class BaseContentCreatePage extends BaseCreateRecord implements ContentForm
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
    use Translatable {
        ContentFormTrait::updatedActiveLocale insteadof Translatable;
        mountTranslatable as protected overrideMountTranslatableTrait;
    }
    use WithPagination;

    protected function getFormActions(): array
    {
        return [
            $this->getPublishFormAction('create', $this->getModel()),
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label(__('inspirecms::buttons.save_draft.label'))
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

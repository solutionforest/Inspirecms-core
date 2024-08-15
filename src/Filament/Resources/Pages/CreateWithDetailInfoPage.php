<?php

namespace SolutionForest\InspireCms\Filament\Resources\Pages;

use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Alignment;

/**
 * @property ?Form $detailInfoForm
 */
class CreateWithDetailInfoPage extends CreateRecord
{
    protected static string $view = 'inspirecms::filament.pages.cms-pages.create';

    public array $detailInfoData = [];

    public static string | Alignment $formActionsAlignment = Alignment::End;

    public function mount(): void
    {
        parent::mount();

        if ($this->hasDetailInfoForm()) {
            $this->detailInfoForm->fill();
        }
    }

    protected function getForms(): array
    {
        $forms = parent::getForms();

        if (method_exists(static::getResource(), 'detailInfoForm')) {

            $forms['detailInfoForm'] = $this->form(static::getResource()::detailInfoForm(
                $this->makeForm()
                    ->operation('create')
                    ->model($this->getModel())
                    ->statePath('detailInfoData')
                    ->columns($this->hasInlineLabels() ? 1 : 2)
                    ->inlineLabel($this->hasInlineLabels()),
            ));
        }

        return $forms;
    }

    public function wrapMainFormBySection(): bool
    {
        return true;
    }

    public function hasDetailInfoForm(): bool
    {
        return data_get($this->getForms(), 'detailInfoForm') != null;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($this->hasDetailInfoForm()) {

            $detailInfoData = $this->detailInfoForm->getState();

            $data = array_merge($data, $detailInfoData);
        }

        return $data;
    }
}

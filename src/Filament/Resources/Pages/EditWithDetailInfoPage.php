<?php

namespace SolutionForest\InspireCms\Filament\Resources\Pages;

use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;

class EditWithDetailInfoPage extends EditRecord
{
    protected static string $view = 'inspirecms::filament.pages.cms-pages.edit';

    public array $detailInfoData = [];

    public static string | Alignment $formActionsAlignment = Alignment::End;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        if ($this->hasDetailInfoForm()) {

            $extraData = [];
            $data = $this->mutateFormDataBeforeFill([
                ...$this->getRecord()->attributesToArray(),
                ...$extraData,
            ]);

            $this->detailInfoForm->fill($data);
        }
    }

    protected function getForms(): array
    {
        $forms = parent::getForms();

        if (method_exists(static::getResource(), 'detailInfoForm')) {

            $forms['detailInfoForm'] = $this->form(static::getResource()::detailInfoForm(
                $this->makeForm()
                    ->operation('edit')
                    ->model($this->getRecord())
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

    public function hasDetailInfoForm()
    {
        return data_get($this->getForms(), 'detailInfoForm') != null;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->hasDetailInfoForm()) {

            $detailInfoData = $this->detailInfoForm->getState();

            $data = array_merge($data, $detailInfoData);
        }

        return $data;
    }
}

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

    protected array $detailInfoDataAfterMutate = [];

    public static string | Alignment $formActionsAlignment = Alignment::End;

    protected function getForms(): array
    {
        $forms = parent::getForms();

        if (method_exists(static::getResource(), 'detailInfoForm')) {

            $forms['detailInfoForm'] = $this->form(static::getResource()::detailInfoForm(
                $this->makeForm()
                    ->operation('create')
                    ->model($this->getModel())
                    ->statePath($this->getDetailInfoFormStatePath())
                    ->columns($this->hasInlineLabels() ? 1 : 2)
                    ->inlineLabel($this->hasInlineLabels()),
            ));
        }

        return $forms;
    }

    public function wrapMainFormBySection(): bool
    {
        return false;
    }

    public function wrapDetailInfoFormBySection(): bool
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

            if ($this->getDetailInfoFormStatePath() == $this->getFormStatePath()) {

                $data = array_merge($data, $detailInfoData);

            } else {
                $this->detailInfoDataAfterMutate = $detailInfoData;
            }
        }


        return $data;
    }

    protected function getDetailInfoFormStatePath(): string
    {
        return 'detailInfoData';
    }
}

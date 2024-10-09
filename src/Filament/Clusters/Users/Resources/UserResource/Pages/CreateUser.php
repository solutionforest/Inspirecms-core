<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Users\Resources\UserResource\Pages;

use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseCreatePage;
use SolutionForest\InspireCms\Filament\Clusters\Users\Resources\UserResource;

class CreateUser extends BaseCreatePage
{
    public function getActions(): array
    {
        return [];
    }

    public static function getResource(): string
    {
        return config('inspirecms.filament.resources.user', UserResource::class);
    }

    protected function getForms(): array
    {
        $resource = static::getResource();

        $formName = 'form';

        if (method_exists($resource, 'createForm')) {
            $formName = 'createForm';
        }

        return [
            'form' => $this->form($resource::{$formName}(
                $this->makeForm()
                    ->operation('create')
                    ->model($this->getModel())
                    ->statePath($this->getFormStatePath())
                    ->columns($this->hasInlineLabels() ? 1 : 2)
                    ->inlineLabel($this->hasInlineLabels()),
            )),
        ];
    }
}

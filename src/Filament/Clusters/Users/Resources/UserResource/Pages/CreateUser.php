<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Users\Resources\UserResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Alignment;
use SolutionForest\InspireCms\Filament\Clusters\Users\Resources\UserResource;

class CreateUser extends CreateRecord
{
    protected static bool $canCreateAnother = false;

    public function getActions(): array
    {
        return [];
    }

    public function getFormActionsAlignment(): string | Alignment
    {
        return 'end';
    }

    public static function getResource(): string
    {
        return config('inspirecms.resources.user', UserResource::class);
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

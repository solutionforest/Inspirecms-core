<?php

namespace SolutionForest\InspireCms\Filament\Resources\UserResource\Pages;

use Illuminate\Auth\Events\Registered;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseCreateRecord;
use SolutionForest\InspireCms\Filament\Resources\UserResource;
use SolutionForest\InspireCms\InspireCmsConfig;

class CreateUser extends BaseCreateRecord
{
    public function getActions(): array
    {
        return [];
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('user', UserResource::class);
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

    public function afterCreate()
    {
        $user = $this->record;

        if ($user && $user instanceof \Illuminate\Contracts\Auth\Authenticatable) {
            event(new Registered($user));
        }
    }
}

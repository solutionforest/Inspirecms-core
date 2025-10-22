<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents\Schemas\Components;

use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Model;
use Pboivin\FilamentPeek\Livewire\BuilderEditor;
use SolutionForest\InspireCms\Base\Filament\Contracts\ContentForm as ContractsContentForm;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content as ModelsContent;

class ContentPropertyDataGroup
{
    public static function make(bool $isTab = false): Tab | Group
    {
        $getFieldGroupsFromDocumentType = function (int | string | Model | null $documentType) {

            if ($documentType instanceof Model) {

                $documentType->loadMissing('fieldGroups.fields');

            } elseif (! is_null($documentType)) {

                $documentType = InspireCmsConfig::getDocumentTypeModelClass()::query()
                    ->with(['fieldGroups.fields']) // build filament fields
                    ->whereHas('fieldGroups')
                    ->find($documentType);
            }

            if (! $documentType) {
                return collect();
            }

            return collect($documentType->fieldGroups)->sortBy('pivot.order')->values();
        };

        $getFieldGroupsFromLivewireOrRecord = function ($livewire, null | Model | ModelsContent $record) use ($getFieldGroupsFromDocumentType) {
            if ($record) { // edit/view page
                $record->documentType?->loadMissing('fieldGroups.fields');
                $fieldGroups = collect($record->documentType->fieldGroups)->sortBy('pivot.order')->values();
            } elseif ($livewire instanceof ContractsContentForm) { // create
                $fieldGroups = $getFieldGroupsFromDocumentType($livewire->getDocumentType() ?? null);
            } elseif ($livewire instanceof BuilderEditor) { // preview builder
                $fieldGroups = $getFieldGroupsFromDocumentType($livewire->editorData['documentType'] ?? null);
            } else {
                $fieldGroups = collect();
            }

            return $fieldGroups;
        };

        $schema = function ($livewire, null | Model | ModelsContent $record) use ($getFieldGroupsFromLivewireOrRecord) {
            $fieldGroups = $getFieldGroupsFromLivewireOrRecord($livewire, $record);

            $groupComponents = [];

            foreach ($fieldGroups as $fieldGroupModel) {

                $groupComponents[] = $fieldGroupModel->toFilamentComponent();

            }

            return $groupComponents;
        };

        if ($isTab) {

            return Tab::make('content')
                ->label(__('inspirecms::resources/content.tabs.content'))
                ->key('propertyData')
                ->statePath('propertyData')
                ->dehydratedWhenHidden()
                ->dehydrateStateUsing(fn ($component) => $component->getState())
                ->visible(fn ($livewire, $record) => count($getFieldGroupsFromLivewireOrRecord($livewire, $record)) > 0)
                ->schema($schema);
        }

        return Group::make()
            ->key('propertyData')
            ->statePath('propertyData')
            ->columnSpanFull()
            ->schema($schema)
            ->dehydrateStateUsing(fn ($component) => $component->getState());
    }
}

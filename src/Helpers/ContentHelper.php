<?php

namespace SolutionForest\InspireCms\Helpers;

use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Facades\PermissionManifest;
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\ContentForm;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;

class ContentHelper
{
    /**
     * Handles the publishable record.
     *
     * @param  Model & Content  $record  The record to be handled.
     * @param  string  $publishableState  The state of the publishable record.
     * @param  EditRecord|CreateRecord  $livewire  The Livewire component instance.
     * @param  array  $publishableData  The data related to the publishable record.
     * @return void
     */
    public static function handlePublishableRecord($record, $publishableState, $livewire, array $publishableData)
    {
        if (! $livewire instanceof ContentForm) {
            throw new \RuntimeException('The Livewire component must implement ContentForm.');
        }

        if ($livewire instanceof EditRecord) {

            $isSuccess = $livewire->handlePublishableRecord(function () use ($publishableData, $livewire, $publishableState) {

                $data = $livewire->getPublishableFormDataBeforePublish();

                $livewire->handlePublishableRecordCreateOrUpdate($data, $publishableData, false, $publishableState);
            });

            if (! $isSuccess) {
                return false;
            }

        } else {

            $record->setPublishableState($publishableState);

            $record->save();

        }

        return true;
    }

    public static function havePermissionToViewNode($id, $user = null)
    {
        $user ??= filament()->auth()->user();
        if (! $user || ($user != null && ! is_inspirecms_user($user))) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        /** @var class-string<Model | Content> */
        $model = InspireCmsConfig::getContentModelClass();
        $rootId = app($model)->getRootLevelParentId();
        if ($id == $rootId) {
            return true;
        }

        $coreCheck = PermissionManifest::authorizeModel(
            ability: 'view',
            model: $model,
        );
        if ($coreCheck === true) {
            return true;
        }

        $tieredCheck = PermissionManifest::authorizeModel(
            ability: 'view',
            model: $model,
            id: $id,
        );
        if ($tieredCheck === true) {
            return true;
        }

        return false;
    }
}

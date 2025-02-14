<?php

namespace SolutionForest\InspireCms\Helpers;

use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Facades\PermissionManifest;
use SolutionForest\InspireCms\Filament\Clusters\Content\Contracts\ContentForm;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;
use Spatie\Permission\Contracts\Permission;

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

    /**
     * @return true|array Returns true if the user has permission to all content, or an array of accessible content IDs if the user has limited permissions.
     */
    public static function getAccessibleContentIds($user = null)
    {
        $user ??= filament()->auth()->user();
        // if (! $user || ($user != null && ! is_inspirecms_user($user))) {
        //     return false;
        // }

        // if ($user->isSuperAdmin()) {
        //     return true;
        // }

        $coreCheck = PermissionManifest::authorizeModel(
            ability: 'view',
            model: static::getModel(),
        );
        if ($coreCheck === true) {
            return true;
        }
        // $coreCheck = collect($accessibleActions)
        //     ->map(fn ($action) => PermissionManifest::authorizeModel(
        //         ability: $action,
        //         model: static::getModel(),
        //     ))
        //     ->where(fn ($result) => $result === true);
        // if ($coreCheck->isNotEmpty()) {
        //     return true;
        // }

        return PermissionHelper::getWildcardPermissions(static::getModel())
            ->map(function (Model | Permission $permission) {

                $accessibleActions = ['view', 'update'];

                $list = PermissionHelper::explodeWildcardPermission($permission->name);

                if (isset($list['action']) && in_array($list['action'], $accessibleActions)) {
                    return $list['id'] ?? null;
                }

                return null;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return class-string<Model | Content>
     */
    private static function getModel()
    {
        return InspireCmsConfig::getContentModelClass();
    }
}

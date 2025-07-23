<?php

namespace SolutionForest\InspireCms\Helpers;

use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\AbstractPaginator;
use SolutionForest\InspireCms\Base\Filament\Contracts\ContentForm;
use SolutionForest\InspireCms\Collection\ContentCollection;
use SolutionForest\InspireCms\Facades\PermissionManifest;
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

        $coreCheck = PermissionManifest::authorizeModel(
            ability: 'view',
            model: static::getModel(),
        );
        if ($coreCheck === true) {
            return true;
        }

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

    public static function getDtoRequiredRelations(): array
    {
        return [
            'documentType.fields.group',
            'documentType.templates',
            'webSetting',
            'publishedVersions',
            'templates',
            'ancestorsAndSelf.webSetting',
        ];
    }

    /**
     * @param  Paginator|AbstractPaginator  $paginator
     * @return Paginator
     */
    public static function initializePaginatorCollection($paginator)
    {
        if ($paginator instanceof Paginator) {

            $items = $paginator->getCollection();

            // for "toDto" method
            if ($items instanceof ContentCollection) {
                $items = $items->setPaginator($paginator);
            }

            $paginator->setCollection($items);

        }

        return $paginator;
    }

    /**
     * @return class-string<Model | Content>
     */
    private static function getModel()
    {
        return InspireCmsConfig::getContentModelClass();
    }
}

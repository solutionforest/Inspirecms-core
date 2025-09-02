<?php

namespace SolutionForest\InspireCms\Filament\Resources\Users\Schemas\Components;

use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TableSelect;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\Size;
use Filament\Support\Services\RelationshipJoiner;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Resources\Roles\Tables\RolesTable;
use SolutionForest\InspireCms\Helpers\AuthHelper;
use SolutionForest\InspireCms\InspireCmsConfig;

class UserRolePicker
{
    public static function make(): Repeater
    {
        $permission = inspirecms_permissions()->getPermissionNameForModel('adjust_roles', InspireCmsConfig::getUserModelClass());

        return Repeater::make('roles')
            ->label(__('inspirecms::resources/user.roles.label'))
            ->validationAttribute(__('inspirecms::resources/user.roles.validation_attribute'))
            ->defaultItems(0)
            ->relationship('roles', fn ($query) => $query->whereGuardName(AuthHelper::guardName()))
            ->deleteAction(fn (Action $action) => $action->authorize($permission))
            ->addAction(fn (Action $action) => static::configureAddAction($action->authorize($permission)))
            ->simple(TextInput::make('name')->disabled())
            ->dehydrated(false)
            ->saveRelationshipsUsing(function (Model $record, array $state) {
                $roles = collect($state)
                    ->pluck('name')
                    ->all();
                $record->syncRoles($roles);
            });
    }

    protected static function configureAddAction(Action $action): Action
    {
        $modelName = strtolower(__('inspirecms::inspirecms.role.singular'));

        return $action
            ->extraAttributes(['class' => 'w-full'], true)
            ->label(fn () => __('inspirecms::buttons.add_with_name.label', [
                'name' => $modelName,
            ]))
            ->modalSubmitActionLabel(__('inspirecms::buttons.add.label'))
            ->modalHeading(__('inspirecms::buttons.add_with_name.heading', [
                'name' => $modelName,
            ]))
            ->color('gray')
            ->button()
            ->size(Size::Medium)
            ->fillForm(function ($state) {
                $ids = collect($state)
                    ->map(function ($data, $key) {
                        return $data['__id'] // Temporary id after adding
                            ?? $data['id']  // Id from existing record
                            ?? null;
                    })
                    ->unique()
                    ->values()
                    ->map(fn ($id) => (string) $id) // Ensure IDs are strings
                    ->toArray();

                return ['selection' => $ids];
            })
            ->schema(fn (Repeater $component) => [
                TableSelect::make('selection')
                    ->hiddenLabel()
                    ->relationshipName($component->getRelationshipName())
                    ->multiple()
                    ->maxItems($component->getMaxItems())
                    ->tableConfiguration(RolesTable::class)
                    ->tableArguments([]),
            ])
            ->action(function (array $data, Action $action, Repeater $component) {

                $relationship = $component->getRelationship();

                $query = app(RelationshipJoiner::class)->prepareQueryForNoConstraints($relationship);

                $roles = $query->find($data['selection']);

                $simpleFieldName = 'name';

                foreach ($roles as $role) {

                    $items = $component->getState();

                    // Skip if the role already exists
                    if (in_array($role->{$simpleFieldName}, array_column($items, $simpleFieldName))) {
                        continue;
                    }

                    $itemState = [
                        $simpleFieldName => $role->{$simpleFieldName},
                        '__id' => $role->getKey(),
                    ];

                    $newUuid = $component->generateUuid();

                    if ($newUuid) {
                        $items[$newUuid] = $itemState;
                    } else {
                        $items[] = $itemState;
                    }

                    $component->state($items);

                    // $component->getChildSchema($newUuid ?? array_key_last($items))->fill($itemState);

                    // $component->collapsed(false, shouldMakeComponentCollapsible: false);

                    $component->callAfterStateUpdated();

                }
            });
    }
}

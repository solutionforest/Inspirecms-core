<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class TieredPermissionsRepeater extends Field
{
    public array $permissions = [];

    public ?string $relatedModel = null;

    public ?Closure $recordTitleUsing = null;

    /**
     * @var view-string
     */
    protected string $view = 'inspirecms::filament.forms.components.tiered-permissions-repeater';

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(function (TieredPermissionsRepeater $component, $state) {
            $state ??= [];
            $component->state($state);
        });

        $this->registerActions([
            fn (TieredPermissionsRepeater $component): Action => $component->getAddAction(),
            fn (TieredPermissionsRepeater $component): Action => $component->getEditAction(),
            fn (TieredPermissionsRepeater $component): Action => $component->getDeleteAction(),
        ]);
    }

    public function permissions(array $permissions): static
    {
        $this->permissions = $permissions;

        return $this;
    }

    public function tieredModel(string $model): static
    {
        $this->relatedModel = $model;

        return $this;
    }

    public function recordTitleUsing(Closure $callback): static
    {
        $this->recordTitleUsing = $callback;

        return $this;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function getRelatedModel(): ?string
    {
        return $this->relatedModel;
    }

    public function getRecordTitle(Model $record): ?string
    {
        return $this->evaluate($this->recordTitleUsing, [
            'record' => $record,
        ]);
    }

    public function getFormattedStateForDisplay($state = null)
    {
        $state ??= $this->getState();
        $query = $this->getEloquentQuery();
        if (! $state || ! $query) {
            return [];
        }

        $groupedPermissions = collect($state)->groupBy(fn ($name) => Arr::last(explode('.', $name)))->toArray();

        $records = $query->whereKey(array_keys($groupedPermissions))->get();

        $recordTitles = $records
            ->mapWithKeys(fn (Model $record) => [
                $record->getKey() => $this->getRecordTitle($record) ?? ($record->hasAttribute('title') ? $record->title : $record->getKey()),
            ])
            ->toArray() ?? [];

        $results = [];

        foreach ($groupedPermissions as $key => $permissionNames) {
            $results[] = [
                'key' => $key,
                'title' => $recordTitles[$key] ?? $key,
                'permissions' => collect($permissionNames)
                    ->mapWithKeys(fn ($name) => [
                        $name => $this->getPermissionDisplayName($name),
                    ])->toArray(),
            ];
        }

        return $results;
    }

    public function getAddAction()
    {
        return Action::make('add')
            ->label(__('inspirecms::buttons.add.label'))
            ->icon(FilamentIcon::resolve('inspirecms::add'))
            ->color('gray')
            ->extraAttributes([
                'class' => 'w-full',
            ])
            ->slideOver()->modalWidth('5xl')
            ->steps(fn (TieredPermissionsRepeater $component) => static::getSteps($component->getPermissions(), 'create'))
            ->action(function (array $data, TieredPermissionsRepeater $component) {
                $permissions = $data['permissions'] ?? [];
                $id = $data['target'][0] ?? null;
                if (empty($permissions) || is_null($id)) {
                    return;
                }

                $state = collect(static::dehydratedPermissionNameForItem($permissions, $id))
                    ->merge($component->getState())
                    ->unique()
                    ->values()
                    ->all();

                $component->state($state);
            });
    }

    public function getEditAction()
    {
        return Action::make('edit')
            ->label(__('inspirecms::buttons.edit.label'))
            ->icon(FilamentIcon::resolve('inspirecms::edit'))
            ->iconButton()
            ->color('primary')
            ->slideOver()->modalWidth('5xl')
            ->fillForm(function (array $arguments, TieredPermissionsRepeater $component) {
                $itemKey = $arguments['itemKey'] ?? null;
                if (is_null($itemKey)) {
                    return [];
                }

                $targetPermissions = collect($component->getState())
                    ->filter(fn ($name) => Str::endsWith($name, '.' . $itemKey))
                    ->map(fn ($name) => Str::beforeLast($name, '.'))
                    ->toArray();

                return [
                    'target' => [$itemKey],
                    'permissions' => $targetPermissions,
                ];
            })
            ->skippableSteps()
            ->startOnStep(2)
            ->steps(fn (TieredPermissionsRepeater $component) => static::getSteps($component->getPermissions(), 'edit'))
            ->action(function (array $arguments, array $data, TieredPermissionsRepeater $component) {
                $permissions = $data['permissions'] ?? [];
                $id = $arguments['itemKey'] ?? null;
                if (empty($permissions) || is_null($id)) {
                    return;
                }

                $component->state(static::dehydratedPermissionNameForItem($permissions, $id));
            });
    }

    public function getDeleteAction()
    {
        return Action::make('delete')
            ->icon(FilamentIcon::resolve('inspirecms::delete'))
            ->iconButton()
            ->color('danger')
            ->action(function (array $arguments, TieredPermissionsRepeater $component) {
                $id = $arguments['itemKey'] ?? null;
                $current = $component->getState() ?? [];

                $newState = collect($current)
                    ->filter(fn ($name) => ! Str::endsWith($name, '.' . $id))
                    ->toArray();

                $component->state($newState);
            });
    }

    /**
     * @return ?Builder
     */
    protected function getEloquentQuery()
    {
        $model = $this->getRelatedModel();
        if (! $model) {
            return null;
        }

        // Determine the model class
        $modelClass = $model;
        if (! class_exists($modelClass) || ! is_a($modelClass, Model::class, true)) {
            return null;
        }

        return $modelClass::query();
    }

    protected function getPermissionDisplayName(string $permission): string
    {
        $checkKey = Str::beforeLast($permission, '.');

        return $this->getPermissions()[$checkKey] ?? $permission;
    }

    protected static function getSteps(array $permissions, string $operation)
    {
        return [
            Step::make(__('inspirecms::resources/role.tiered_permissions.steps.target.label'))
                ->schema([
                    ContentTree::make('target')
                        ->hiddenLabel()
                        ->validationAttribute(__('inspirecms::resources/role.tiered_permissions.steps.target.validation_attribute'))
                        ->maxItems(1)
                        ->minItems(1)
                        ->required()
                        ->disabled(fn () => $operation === 'edit')
                        ->filteringByPermission(false),
                ]),
            Step::make(__('inspirecms::resources/role.tiered_permissions.steps.access_control.label'))
                ->schema([
                    CheckboxList::make('permissions')
                        ->hiddenLabel()
                        ->validationAttribute(__('inspirecms::resources/role.permissions.validation_attribute'))
                        ->options($permissions)
                        ->columns(3)
                        ->gridDirection('row')
                        ->bulkToggleable(),
                ]),
        ];
    }

    protected static function dehydratedPermissionNameForItem(array $permissions, $id)
    {
        return collect($permissions)
            ->map(fn ($name) => implode('.', [$name, $id]))
            ->unique()
            ->filter()
            ->values()
            ->all();
    }
}

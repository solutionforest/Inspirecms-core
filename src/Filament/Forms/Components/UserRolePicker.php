<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Services\RelationshipJoiner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class UserRolePicker extends Repeater
{
    protected ?Closure $modifyRecordSelectUsing = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->defaultItems(0);

        $this->relationship(
            'roles', 
        );

        $this->simple(TextInput::make('name')->disabled());

        $this->dehydrated(false);

        $this->saveRelationshipsUsing(function (Model $record, array $state) {
            
            $roles = collect($state)
                ->pluck('name')
                ->toArray();
                
            $record->syncRoles($roles);
        });
    }

    public function getGuardName(): string
    {
        return InspireCmsConfig::getGuardName();
    }

    public function modifyRecordSelectUsing(?Closure $callback): static
    {
        $this->modifyRecordSelectUsing = $callback;

        return $this;
    }


    public function getRelationshipQuery()
    {
        $relationship = $this->getRelationship();

        $query = app(RelationshipJoiner::class)->prepareQueryForNoConstraints($relationship);

        $this->evaluate($this->modifyRelationshipQueryUsing, [
            'query' => $query,
        ]);

        return $query;
    }

    public function getRecordSelect(): Select
    {
        $select = Select::make('recordId')
            ->hiddenLabel()
            ->required()
            ->relationship('roles', 'name', function (Builder $query) {

                $existingNames = collect($this->getState())->pluck('name')->filter()->all();

                if (count($existingNames) > 0) {
                    $query->whereNotIN('name', $existingNames);
                }

                return $query->where('guard_name', $this->getGuardName());

            })
            ->preload()
            ->searchable();

        if ($this->modifyRecordSelectUsing) {
            $select = $this->evaluate($this->modifyRecordSelectUsing, [
                'select' => $select,
            ]);
        }

        return $select;
    }

    public function getAddAction(): Action
    {
        $action = Action::make($this->getAddActionName())
            ->label(fn (Repeater $component) => $component->getAddActionLabel())
            ->color('gray')
            ->form(function (Form $form): array | Form | null {
                return $form
                    ->schema(fn () => [
                        $this->getRecordSelect(),
                    ]);
            })
            ->action(function (UserRolePicker $component, array $data, Form $form): void {

                $role = $this->getRelationshipQuery()->find($data['recordId']);

                if ($role) {

                    $itemState = [
                        'name' => $role->name,
                    ];
    
                    $newUuid = $component->generateUuid();
    
                    $items = $component->getState();
    
                    if ($newUuid) {
                        $items[$newUuid] = $itemState;
                    } else {
                        $items[] = $itemState;
                    }
    
                    $component->state($items);
    
                    $component->getChildComponentContainer($newUuid ?? array_key_last($items))->fill($itemState);
    
                    $component->collapsed(false, shouldMakeComponentCollapsible: false);
    
                    $component->callAfterStateUpdated();

                }
            })
            ->button()
            ->size(ActionSize::Medium)
            ->visible(fn (Repeater $component): bool => $component->isAddable());

        if ($this->modifyAddActionUsing) {
            $action = $this->evaluate($this->modifyAddActionUsing, [
                'action' => $action,
            ]) ?? $action;
        }

        return $action;
    }
}

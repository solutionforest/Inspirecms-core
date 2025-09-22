<?php

namespace SolutionForest\InspireCms\Fields\Configs\Concerns;

use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Support\Icons\Heroicon;

trait EditorBasicTrait
{
    public string $toolbarButtonType = 'buttons'; // or 'buttonGroups'

    public array $toolbarButtons = [];

    public array $toolbarButtonGroups = [];

    public ?string $fileAttachmentsDisk = null;

    public ?string $fileAttachmentsDirectory = null;

    public ?string $fileAttachmentsVisibility = null;

    protected static function getEditorBasicTraitComponent($name)
    {
        $defaultDisk = config('filesystems.default', 'public');
        $disks = config('filesystems.disks', []);

        return match ($name) {

            'toolbarButtonType' => ToggleButtons::make('toolbarButtonType')
                ->label('Toolbar Button Type')
                ->options(static::getAvailableToolbarTypes())
                ->grouped()
                ->live()
                ->default('buttons')
                ->afterStateHydrated(function ($state, ToggleButtons $component) {
                    $component->state($state ?? 'buttons');
                })
                ->required(),

            'toolbarButtons' => TagsInput::make('toolbarButtons')
                ->placeholder('Add Toolbar Button (Enter to add)')
                ->suggestions(static::getAllAvailableToolbarButtons())
                ->reorderable()
                ->suffixAction(
                    Action::make('appendButton')
                        ->icon(Heroicon::Plus)
                        ->fillForm(['buttons' => []])
                        ->schema([
                            CheckboxList::make('buttons')
                                ->options(static::getAllAvailableToolbarButtonsOptions())
                                ->columns(3)
                                ->bulkToggleable()
                                ->hiddenLabel(),
                        ])
                        ->action(function ($state, $data, $set) {
                            $original = $state ?? [];
                            // append new buttons to original state
                            $new = array_values(array_diff($data['buttons'] ?? [], $original));
                            $state = array_merge($original, $new);
                            $set('toolbarButtons', $state);
                        })
                        ->size('sm')
                )
                ->suffixAction(
                    Action::make('clearAll')
                        ->label('Clear All')
                        ->icon(Heroicon::XMark)
                        ->color('danger')
                        ->action(function ($state, $set) {
                            $set('toolbarButtons', []);
                        })
                        ->size('sm')
                ),

            'toolbarButtonGroups' => Repeater::make('toolbarButtonGroups')
                ->table([
                    TableColumn::make('Buttons'),
                ])
                ->schema([
                    TagsInput::make('buttons')
                        ->suggestions(static::getAllAvailableToolbarButtons())
                        ->reorderable()
                        ->placeholder('Add Toolbar Button (Enter to add)'),
                ])
                ->addActionLabel('Add Group')
                ->collapsible()
                ->cloneable()
                ->extraItemActions([
                    Action::make('appendButton')
                        ->icon(Heroicon::Plus)
                        ->fillForm(['buttons' => []])
                        ->schema([
                            CheckboxList::make('buttons')
                                ->options(static::getAllAvailableToolbarButtonsOptions())
                                ->columns(3)
                                ->bulkToggleable()
                                ->hiddenLabel(),
                        ])
                        ->action(function ($data, array $arguments, Repeater $component) {
                            $state = $component->getState() ?? [];
                            $itemKey = $arguments['item'] ?? null;
                            if (empty($itemKey) || ! isset($state[$itemKey])) {
                                return;
                            }
                            $original = $state[$itemKey]['buttons'] ?? [];
                            // append new buttons to original state
                            $new = array_values(array_diff($data['buttons'] ?? [], $original));
                            $state[$itemKey]['buttons'] = array_merge($original, $new);
                            $component->state($state);
                        })
                        ->size('sm'),
                ]),

            'fileAttachmentsDisk' => Select::make('fileAttachmentsDisk')->label('Disk')
                ->default($defaultDisk)
                ->options(collect($disks)->keys()->mapWithKeys(fn ($disk) => [$disk => $disk])->all()),
            'fileAttachmentsDirectory' => TextInput::make('fileAttachmentsDirectory')->label('Directory'),
            'fileAttachmentsVisibility' => Select::make('fileAttachmentsVisibility')->label('Visibility')
                ->default($disks[$defaultDisk]['visibility'] ?? 'public')
                ->options([
                    'public' => 'Public',
                    'private' => 'Private',
                ]),
            default => null,
        };
    }

    public static function getAllAvailableToolbarButtons(): array
    {
        if (isset(static::$availableToolbarButtons) && is_array(static::$availableToolbarButtons) && ! empty(static::$availableToolbarButtons)) {
            return static::formatAsSelectableArray(collect(static::$availableToolbarButtons)->flatten()->values()->all());
        }

        return [];
    }

    protected static function getAllAvailableToolbarButtonsOptions(): array
    {
        $result = [];
        foreach (static::getAllAvailableToolbarButtons() as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, static::formatAsSelectableArray($value));
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    protected static function formatAsSelectableArray(array $items): array
    {
        $result = [];
        foreach ($items as $item) {
            if (is_array($item)) {
                $result[] = static::formatAsSelectableArray($item);
            } else {
                $result[$item] = $item;
            }
        }

        return $result;
    }

    protected static function getAvailableToolbarTypes(): array
    {
        if (isset(static::$availableToolbarButtonTypes) && is_array(static::$availableToolbarButtonTypes) && ! empty(static::$availableToolbarButtonTypes)) {
            return static::$availableToolbarButtonTypes;
        }

        return [
            'buttons' => 'Buttons',
            'buttonGroups' => 'Button Groups',
        ];
    }
}

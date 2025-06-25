<?php

namespace SolutionForest\InspireCms\Fields\Configs\Concerns;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

trait EditorBasicTrait
{
    public array $toolbarButtons = [];

    public ?string $fileAttachmentsDisk = null;

    public ?string $fileAttachmentsDirectory = null;

    public ?string $fileAttachmentsVisibility = null;

    protected static function getEditorBasicTraitComponent($name)
    {
        $defaultDisk = config('filesystems.default', 'public');
        $disks = config('filesystems.disks', []);

        return match ($name) {
            'toolbarButtons' => CheckboxList::make('toolbarButtons')
                ->options(static::getAllAvailableToolbarButtons())
                ->bulkToggleable()
                ->columns(3),

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
            return collect(static::$availableToolbarButtons)
                ->values()
                ->mapWithKeys(fn ($button) => [$button => $button])
                ->all();
        }

        return [];
    }
}

<?php

namespace SolutionForest\InspireCms\Fields\Configs\Concerns;

use Filament\Forms;

trait EditorBasicTrait
{
    public array $toolbarButtons = [];

    public ?string $fileAttachmentsDisk = null;

    public ?string $fileAttachmentsDirectory = null;

    public ?string $fileAttachmentsVisibility = null;

    protected static function getEditorBasicTraitComponent($name)
    {
        $defaultDisk = config('filesystems.default', 'public');
        return match ($name) {
            'toolbarButtons' => Forms\Components\CheckboxList::make('toolbarButtons')
                ->options(static::getAllAvailableToolbarButtons())
                ->bulkToggleable()
                ->columns(3),

            'fileAttachmentsDisk' => Forms\Components\TextInput::make('fileAttachmentsDisk')->label('Disk')->default($defaultDisk),
            'fileAttachmentsDirectory' => Forms\Components\TextInput::make('fileAttachmentsDirectory')->label('Directory'),
            'fileAttachmentsVisibility' => Forms\Components\TextInput::make('fileAttachmentsVisibility')->label('Visibility')->default(config("filesystems.disks.{$defaultDisk}.visibility")),
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

<?php

namespace SolutionForest\InspireCms\Filament\Tables\Actions;

use Filament\Actions\Action;
use Filament\Support\Facades\FilamentIcon;
use Pboivin\FilamentPeek\Facades\Peek;
use Pboivin\FilamentPeek\Support\Concerns\SetsInitialPreviewModalData;

class EditAndPreviewAction extends Action
{
    use SetsInitialPreviewModalData;

    public static int $count = 1;

    protected ?string $builderField = null;

    public static function getDefaultName(): ?string
    {
        return 'editAndPreview';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('inspirecms::buttons.edit_and_preview.label'))
            ->icon(FilamentIcon::resolve('inspirecms::edit'))
            ->color('primary')
            ->action(function ($livewire) {
                Peek::ensurePluginIsLoaded();

                Peek::ensurePageSupportsPreviewModal($livewire);

                if ($this->builderField) {
                    Peek::ensurePageSupportsBuilderPreview($livewire);

                    $livewire->openPreviewModalForBuilder($this->builderField);
                } else {
                    $livewire->initialPreviewModalData(
                        $this->evaluate($this->previewModalData)
                    );

                    $livewire->openPreviewModal();
                }
            });

        Peek::registerPreviewModal();
    }

    public function builderPreview(string $builderField = 'blocks'): static
    {
        Peek::registerBuilderEditor();

        $this->builderField = $builderField;

        return $this;
    }

    /** Alias for builderPreview */
    public function builderName(string $builderField = 'blocks'): static
    {
        $this->builderPreview($builderField);

        return $this;
    }
}

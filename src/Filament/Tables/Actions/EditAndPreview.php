<?php

namespace SolutionForest\InspireCms\Filament\Tables\Actions;

use Filament\Support\Facades\FilamentIcon;
use Pboivin\FilamentPeek\Support;

class EditAndPreview extends \Filament\Tables\Actions\Action
{
    use Support\Concerns\SetsInitialPreviewModalData;

    public static int $count = 1;

    protected ?string $builderField = null;

    public static function getDefaultName(): ?string
    {
        return 'edit_and_preview';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('inspirecms::actions.edit_and_preview.label'))
            ->icon(FilamentIcon::resolve('actions::edit-action') ?? 'heroicon-m-pencil-square')
            ->color('primary')
            ->action(function ($livewire) {
                Support\Panel::ensurePluginIsLoaded();

                Support\Page::ensurePreviewModalSupport($livewire);

                if ($this->builderField) {
                    Support\Page::ensureBuilderPreviewSupport($livewire);

                    $livewire->openPreviewModalForBuidler($this->builderField);
                } else {
                    $livewire->initialPreviewModalData(
                        $this->evaluate($this->previewModalData)
                    );

                    $livewire->openPreviewModal();
                }
            });

        Support\View::setupPreviewModal();
    }

    public function builderPreview(string $builderField = 'blocks'): static
    {
        Support\View::setupBuilderEditor();

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

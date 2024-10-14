<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class MediaBrowser extends Field
{
    /**
     * @var view-string
     */
    protected string $view = 'inspirecms::filament.forms.components.media-picker.browser';

    protected array $mimeTypes = ['*'];

    protected bool $multiple = false;

    public function image(): static
    {
        return $this->mimeTypes(['image/*']);
    }

    public function mimeTypes(array $mimeTypes): static
    {
        $this->mimeTypes = $mimeTypes;

        return $this;
    }

    public function multiple(bool $condition = true): static
    {
        $this->multiple = $condition;

        return $this;
    }

    public function getMimeTypes(): array
    {
        return $this->mimeTypes;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }
}

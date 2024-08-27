<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents\PageResource\Contracts;

interface HasPublishForm
{
    /**
     * Get/validate the form state.
     */
    public function getPublishableFormDataBeforePublish(): array;
}

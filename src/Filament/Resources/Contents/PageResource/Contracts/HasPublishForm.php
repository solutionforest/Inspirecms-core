<?php

namespace SolutionForest\InspireCms\Filament\Resources\Contents\PageResource\Contracts;

interface HasPublishForm
{
    /**
     * Get/validate the form state.
     * @return array
     */
    public function getPublishableFormDataBeforePublish(): array;
}

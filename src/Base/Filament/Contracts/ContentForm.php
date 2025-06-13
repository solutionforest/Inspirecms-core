<?php

namespace SolutionForest\InspireCms\Base\Filament\Contracts;

use Closure;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Contracts\DocumentType;

interface ContentForm
{
    /**
     * @return null | Model & DocumentType
     */
    public function getDocumentType();

    public function getParent(): string | int | Model | null;

    public function getParentKey(): string | int | null;

    public function getActiveActionsLocale(): ?string;

    public function validatePublishableData(): void;

    /**
     * Handles the publishable record by executing the provided callback.
     *
     * @param  Closure  $callback  The callback function to handle the publishable record.
     */
    public function handlePublishableRecord(Closure $callback);

    /**
     * Get/validate the form state for publishable data.
     */
    public function getPublishableFormDataBeforePublish(): array;

    /**
     * Handle the creation or update of a publishable record.
     *
     * This method processes the creation or updating of a record,
     * setting its publishable state based on the provided action.
     * It also manages tenant association if applicable.
     *
     * @param  array  $data  The data to create or update the record with.
     * @param  array  $publishableData  The data to set for the publishable state.
     * @param  bool  $isCreating  Indicates if the record is being created (true) or updated (false).
     * @param  string  $publishableAction  The action to set for the publishable state (default is 'draft').
     * @return \Illuminate\Database\Eloquent\Model The created or updated record.
     */
    public function handlePublishableRecordCreateOrUpdate(array $data, array $publishableData, bool $isCreating): Model;
}

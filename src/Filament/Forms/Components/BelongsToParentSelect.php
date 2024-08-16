<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Select;

class BelongsToParentSelect extends Select
{
    public function nestableParentRelationship(string | Closure | null $name = null, string | Closure | null $titleAttribute = null, bool $ignoreRecord = false): static
    {
        return $this->relationship(name: $name, titleAttribute: $titleAttribute, ignoreRecord: $ignoreRecord, modifyQueryUsing: function ($query, $record) {

            // Skip its children
            if ($record) {
                // Exclude the current record and all its descendants
                $descendantIds = $record->descendants()->pluck('id')->toArray();
                $query->whereNotIn('id', $descendantIds);
            }
            return $query;
        });
    }
}

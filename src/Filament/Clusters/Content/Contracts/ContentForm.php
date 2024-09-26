<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Content\Contracts;

use Illuminate\Database\Eloquent\Model;

interface ContentForm
{
    public function getDocumentType(): string | int | Model;
    public function getParent(): string | int | Model | null;
    public function getParentKey(): string | int | null;
}

<?php

namespace SolutionForest\InspireCms\Macros;

use Illuminate\Database\Schema\Blueprint;

/**
 * @see Blueprint
 */
class BlueprintMarcos
{
    public function author()
    {
        return function (string $userType = 'integer') {
            if ($userType === 'integer') {
                $this->morphs('author');
            } else {
                $this->uuidMorphs('author');
            }
        };
    }
}
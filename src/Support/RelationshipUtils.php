<?php

namespace SolutionForest\InspireCms\Support;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Znck\Eloquent\Relations\BelongsToThrough;

class RelationshipUtils
{
    public static function getQualifiedRelatedKeyNameForRelationship(Relation $relationship): string
    {
        if ($relationship instanceof BelongsToMany) {
            return $relationship->getQualifiedRelatedKeyName();
        }

        if ($relationship instanceof HasManyThrough) {
            return $relationship->getQualifiedForeignKeyName();
        }

        if (
            ($relationship instanceof HasOneOrMany) ||
            ($relationship instanceof BelongsToThrough)
        ) {
            return $relationship->getRelated()->getQualifiedKeyName();
        }

        /** @var BelongsTo $relationship */
        return $relationship->getQualifiedOwnerKeyName();
    }
}

<?php

namespace SolutionForest\InspireCms\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ModelHelper
{
    /**
     * @param null|string|class-string<Model>|Model $tableOrModel
     * @param ?string $tableName
     * @return bool
     */
    public static function isTableExists($tableOrModel, &$tableName = null): bool
    {
        $tableName = static::getTableName($tableOrModel);

        return Schema::hasTable($tableName);
    }

    /**
     * @param null|string|class-string<Model>|Model $target
     * @return ?string
     */
    public static function getTableName($target)
    {
        if (static::isModelExists($target)) {
            return app($target)->getTable();
        }

        return $target;
    }

    /**
     * @param class-string|Model $model
     * @return bool
     */
    public static function isModelExists($model)
    {
        if (! static::isModel($model)) {
            return false;
        }
        
        return class_exists($model);
    }

    /**
     * @param mixed $target
     * @return bool
     */
    public static function isModel($target)
    {
        return is_a($target, Model::class, true);
    }
}

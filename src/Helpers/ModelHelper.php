<?php

namespace SolutionForest\InspireCms\Helpers;

use Illuminate\Support\Facades\Schema;

class ModelHelper
{
    /**
     * Check if a table exists in the database.
     *
     * @param  string  $tableName  The name of the table to check.
     * @return bool Returns true if the table exists, false otherwise.
     */
    public static function isTableExists(string &$tableName): bool
    {
        // is class name
        if (class_exists($tableName)) {
            $tableName = app($tableName)->getTable();
        }

        if (! Schema::hasTable($tableName)) {
            return false;
        }

        return true;
    }
}

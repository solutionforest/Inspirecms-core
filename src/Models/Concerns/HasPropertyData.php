<?php

namespace SolutionForest\InspireCms\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

trait HasPropertyData
{
    use HasContentVersions;

    public static function bootHasPropertyData()
    {
        //
    }

    public function createPropertyData(array $data)
    {
        $propertyData = InspireCmsConfig::getPropertyDataModelClass()::create(array_merge($data, $this->getPropertyDateToSave()));

        $this->propertyDatas()->attach($propertyData->getKey(), [
            'version_date' => now(), // Additional pivot data

            // TODO: old_values + new_values
        ]);
    }

    protected function getPropertyDateToSave()
    {
        return [];
    }

    public function propertyDatas(): BelongsToMany
    {
        return $this->belongsToMany(
            InspireCmsConfig::getPropertyDataModelClass(),
            InspireCmsConfig::getContentVersionTableName(), // Pivot table name
            'content_id', // Foreign key on the pivot table
            'property_data_id' // Foreign key on the related model
        );
    }

    // public function latestPropertyData()
    // {
    //     return $this->belongsToMany(
    //         InspireCmsConfig::getPropertyDataModelClass(),
    //         InspireCmsConfig::getContentVersionTableName(), // Pivot table name
    //         'content_id', // Foreign key on the pivot table
    //         'property_data_id' // Foreign key on the related model
    //     )->wherePivot('version_date', fn ($query) => dd($query) &&  $query->orderBy('version_date', 'desc'));
    // }
}

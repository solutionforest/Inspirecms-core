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
            // Additional pivot data
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
        )->withPivot('created_at');
    }

    public function latestPropertyDatas()
    {
        return $this->belongsToMany(
            InspireCmsConfig::getPropertyDataModelClass(),
            InspireCmsConfig::getContentVersionTableName(), // Pivot table name
            'content_id', // Foreign key on the pivot table
            'property_data_id' // Foreign key on the related model
        )->wherePivot('property_data_id', function ($query) {
            return $query
                ->from(InspireCmsConfig::getContentVersionTableName())
                ->where('content_id', $this->getKey())
                ->orderBy('created_at','desc')
                ->select('property_data_id')
                ->limit(1);
        });
    }
}

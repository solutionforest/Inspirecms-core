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
        $propertyData = InspireCmsConfig::getPropertyDataModelClass()::create(array_merge(
            $data,
            $this->getPropertyDateToSave(),
        ));

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

    public function getLatestPropertyData()
    {
        return $this->propertyDatas()
            ->orderByPivot('created_at', 'desc')
            ->first();
    }

    public function getLatestPublishedPropertyData()
    {
        return $this->propertyDatas()
            ->orderByPivot('created_at', 'desc')
            ->whereNotNull('published_at')
            ->first();
    }
}

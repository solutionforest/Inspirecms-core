<?php

namespace SolutionForest\InspireCms\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use SolutionForest\InspireCms\Models\Contracts\PropertyData;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

trait HasPropertyData
{
    use HasContentVersions;

    public static function bootHasPropertyData()
    {
        //
    }

    /** {@inheritDoc} */
    public function propertyDatas(): BelongsToMany
    {
        return $this->belongsToMany(
            InspireCmsConfig::getPropertyDataModelClass(),
            InspireCmsConfig::getContentVersionTableName(), // Pivot table name
            'content_id', // Foreign key on the pivot table
            'property_data_id' // Foreign key on the related model
        )->withPivot('created_at');
    }

    /** {@inheritDoc} */
    public function createPropertyData(array $data)
    {
        $propertyData = InspireCmsConfig::getPropertyDataModelClass()::create(array_merge(
            $data,
            $this->getPropertyDateToSave(),
        ));

        $this->propertyDatas()->attach($propertyData->getKey(), [
            // Additional pivot data
        ]);

        return $propertyData;
    }

    /**
     * Get the property data to save.
     *
     * This method can be overridden in a subclass to provide specific
     * property data that needs to be saved along with the property data
     * creation process.
     *
     * @return array An associative array of property data to be saved.
     */
    protected function getPropertyDateToSave()
    {
        return [];
    }

    /** {@inheritDoc} */
    public function getLatestPropertyData(): ?PropertyData
    {
        return $this->propertyDatas()
            ->orderByPivot('created_at', 'desc')
            ->first();
    }

    /** {@inheritDoc} */
    public function getLatestPublishedPropertyData(): ?PropertyData
    {
        return $this->propertyDatas()
            ->orderByPivot('created_at', 'desc')
            ->whereNotNull('published_at')
            ->first();
    }
}

<?php

namespace SolutionForest\InspireCms\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use SolutionForest\InspireCms\Support\Helpers\KeyHelper;

class ContentFactory extends Factory
{
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(1),
            'slug' => $this->faker->slug,
            'status' => 0,
            'parent_id' => KeyHelper::generateMinUuid(),
            'document_type_id' => 1,
        ];
    }

    public function havePropertyData(array $data)
    {
        return $this->state(function (array $attributes) use ($data) {
            return [
                'propertyData' => json_encode($data),
            ];
        });
    }
}

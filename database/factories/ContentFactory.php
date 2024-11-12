<?php

namespace SolutionForest\InspireCms\Database\Factories;

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
}

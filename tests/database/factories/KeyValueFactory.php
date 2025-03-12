<?php

namespace SolutionForest\InspireCms\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class KeyValueFactory extends Factory
{
    public function definition()
    {
        return [
            'key' => $this->faker->unique()->slug(1),
        ];
    }
}

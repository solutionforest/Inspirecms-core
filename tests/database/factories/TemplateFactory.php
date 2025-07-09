<?php

namespace SolutionForest\InspireCms\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TemplateFactory extends Factory
{
    public function definition()
    {
        return [
            'slug' => $this->faker->unique()->slug(1),
        ];
    }
}

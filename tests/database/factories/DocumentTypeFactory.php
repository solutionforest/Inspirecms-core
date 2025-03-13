<?php

namespace SolutionForest\InspireCms\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentTypeFactory extends Factory
{
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(1),
            'slug' => $this->faker->unique()->slug(1),
            'category' => $this->faker->randomElement([
                'web', 
                'data', 
                // 'inheritance',
            ]),
            'show_as_table' => $this->faker->boolean(),
            'show_at_root' => $this->faker->boolean(),
            'icon' => null,
        ];
    }
}

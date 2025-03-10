<?php

namespace SolutionForest\InspireCms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LanguageFactory extends Factory
{
    public function definition()
    {
        return [
            'code' => $this->faker->unique()->languageCode,
            'is_default' => false,
        ];
    }
}

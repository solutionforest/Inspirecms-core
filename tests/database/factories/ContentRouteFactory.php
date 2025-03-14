<?php

namespace SolutionForest\InspireCms\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ContentRouteFactory extends Factory
{
    public function definition()
    {
        return [
            'content_id' => null,
            'language_id' => null,
            'uri' => $this->faker->url,
            'is_default_pattern' => true,
            'regex_constraints' => null,
        ];
    }
}

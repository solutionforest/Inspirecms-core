<?php

namespace SolutionForest\InspireCms\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FieldFactory extends Factory
{
    public function definition()
    {
        $slug = $this->faker->unique()->slug(1);
        $title = str($slug)->replace('-', ' ')->ucfirst()->toString();
        return [
            'name' => $slug,
            'label' => $title,
            'type' => 'text', // Default type, can be overridden
            'sort' => 0,
            'mandatory' => false,
            'config' => [],
        ];
    }
}

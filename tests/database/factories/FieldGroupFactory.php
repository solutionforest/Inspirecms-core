<?php

namespace SolutionForest\InspireCms\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FieldGroupFactory extends Factory
{
    public function definition()
    {
        $slug = $this->faker->unique()->slug(1);
        $title = str($slug)->replace('-', ' ')->ucfirst()->toString();

        return [
            'title' => $title,
            'name' => $slug,
            'active' => true,
            'sort' => 0,
        ];
    }
}

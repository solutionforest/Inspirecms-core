<?php

namespace SolutionForest\InspireCms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use SolutionForest\InspireCms\Models\DocumentType;

class DocumentTypeFactory extends Factory
{
    protected $model = DocumentType::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(1),
            'slug' => $this->faker->slug(1),
            'category' => 'web',
            'show_children_as_table' => false,
            'parent_id' => 0,
        ];
    }
}

<?php

namespace SolutionForest\InspireCms\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use SolutionForest\InspireCms\Models\ContentPublishVersion;

class ContentPublishVersionFactory extends Factory
{
    protected $model = ContentPublishVersion::class;

    public function definition()
    {
        return [
            'published_at' => now(),
        ];
    }
}

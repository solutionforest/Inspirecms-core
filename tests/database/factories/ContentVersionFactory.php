<?php

namespace SolutionForest\InspireCms\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use SolutionForest\InspireCms\Models\ContentVersion;
use SolutionForest\InspireCms\Support\Helpers\KeyHelper;

class ContentVersionFactory extends Factory
{
    protected $model = ContentVersion::class;

    public function definition()
    {
        return [
            'to_data' => [],
            'from_data' => [],
            'content_id' => KeyHelper::generateMinUuid(),
            'author_id' => KeyHelper::generateMinUuid(),
            'author_type' => 'cms_user',
            'avoid_to_clean' => false,
            'event_name' => 'created',
            'publish_state' => 'draft',
        ];
    }

    public function avoidToClean(bool $condition = true)
    {
        return $this->state(function (array $attributes) use ($condition) {
            return [
                'avoid_to_clean' => $condition,
            ];
        });
    }

    public function withPublishLog()
    {
        return $this->hasPublishLog(1, function (array $attributes, $contentVersion) {
            return ['content_id' => $contentVersion->content_id];
        });
    }
}

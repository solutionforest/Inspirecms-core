<?php

namespace SolutionForest\InspireCms\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ImportFactory extends Factory
{
    public function definition()
    {
        return [
            'available_at' => now(),
            'file_name' => 'test.zip',
        ];
    }

    public function isCompleted()
    {
        return $this->state(function (array $attributes) {
            return [
                'finished_at' => now(),
            ];
        });
    }

    public function isFailed($payload = null)
    {
        return $this->state(function (array $attributes) use ($payload) {
            return [
                'failed_at' => now(),
                'payload' => $payload,
            ];
        });
    }
}

<?php

namespace SolutionForest\InspireCms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Models\Content;
use SolutionForest\InspireCms\Support\Database\Factories\NestableTreeFactory;
use SolutionForest\InspireCms\Support\Helpers\KeyHelper;
use SolutionForest\InspireCms\Support\Models\Contracts\NestableTree as NestableTreeContract;
use SolutionForest\InspireCms\Support\Models\Polymorphic\NestableTree;

class ContentFactory extends Factory
{
    protected $model = Content::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(1),
            'slug' => $this->faker->slug,
            'status' => 0,
            'parent_id' => KeyHelper::generateMinUuid(),
            'document_type_id' => 1,
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this
            ->afterMaking(function (Content $content) {
                //
            })->afterCreating(function (Content $content) {
                /** @var Model */
                $nestableTree = app(NestableTreeContract::class) ?? app(NestableTree::class);

                /** @var Factory */
                $nestableTreeFactory = in_array(HasFactory::class, class_uses($nestableTree)) ?
                    $nestableTree->factory() :
                    new NestableTreeFactory;

                $nestableTreeFactory->create([
                    'nestable_id' => $content->getKey(),
                    'nestable_type' => $content->getMorphClass(),
                ]);
                $content->load('nestableTree');
            });
    }
}

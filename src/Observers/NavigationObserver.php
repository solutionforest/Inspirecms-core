<?php

namespace SolutionForest\InspireCms\Observers;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Base\Enums\Interfaces\NavigationCategory as NavigationCategoryEnumInterface;
use SolutionForest\InspireCms\Base\Enums\Interfaces\NavigationType as NavigationTypeEnumInterface;
use SolutionForest\InspireCms\Base\Enums\NavigationType as NavigationTypeEnum;
use SolutionForest\InspireCms\Facades\InspireCms;
use SolutionForest\InspireCms\Models\Contracts\Navigation;

class NavigationObserver
{
    /**
     * @param  Navigation&Model  $model
     * @return void
     */
    public function saving($model)
    {
        if ($model->type instanceof NavigationTypeEnumInterface) {
            $model->type = $model->type->value;
        }
        switch ($model->type) {
            case NavigationTypeEnum::Content->value:
                $model->url = null;

                break;
            case NavigationTypeEnum::Link->value:
                $model->content_id = null;

                break;
            case NavigationTypeEnum::Group->value:
                $model->content_id = null;
                $model->url = null;

                break;
        }
        if (blank($model->category)) {
            $model->category = 'main';
        }

        // If the category is changed, make the model root
        if ($model->isDirty('category')) {
            $model->makeRoot();
        }
        if (is_null($model->content_id)) {
            $model->content_id = $model->defaultContentId();
        }

        $this->clearCached();
    }

    /**
     * @param  Navigation&Model  $model
     * @return void
     */
    public function deleting($model)
    {
        $this->clearCached();
    }

    protected function clearCached()
    {
        InspireCms::forgetCachedNavigation();
    }
}

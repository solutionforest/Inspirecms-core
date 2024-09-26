<?php

namespace SolutionForest\InspireCms\Filament\Actions;

use Closure;
use Filament\Actions\Action;
use SolutionForest\InspireCms\Base\Filament\Actions\BaseCreateContentAction;
use SolutionForest\InspireCms\Filament\Clusters\Content\Resources\PageResource;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

class CreateContentAction extends BaseCreateContentAction
{
    protected ?Closure $modifyUrlParameterUsing = null;

    public static function getDefaultName(): ?string
    {
        return 'create_content';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $documentTypes = InspireCmsConfig::getDocumentTypeModelClass()::query()
            ->where('is_web_page', true)
            ->get();

        $contentResource = config('inspirecms.resources.page', PageResource::class);
        $contentModel = $contentResource::getModel();

        $documentTypeActions = $documentTypes->map(
            fn ($documentType) => Action::make('create_content_' . $documentType->slug)
                ->label($documentType->title)
                ->url(function () use ($documentType, $contentResource) {
                    try {
                        if ($contentResource::hasPage('create')) {
                            $parameters = ['documentType' => $documentType];

                            if ($this->modifyUrlParameterUsing) {
                                $parameters = $this->evaluate($this->modifyUrlParameterUsing, [
                                    'parameters' => $parameters,
                                    'documentType' => $documentType,
                                ]);
                            }

                            return $contentResource::getUrl('create', $parameters);
                        }
                    } catch (\Throwable $th) {
                        //
                    }

                    return null;
                })
                ->model($contentModel)
                ->hidden(fn (Action $action) => ! $contentResource::can('create') && ! blank($action->getUrl()))
        )->toArray();

        $this->actions($documentTypeActions);

        $this->label(__('inspirecms::actions.create_content.label'));

        $this->model($contentModel);
        $this->hidden(fn () => ! $contentResource::can('create'));
    }

    public function modifyUrlParameterUsing(Closure $callback): static
    {
        $this->modifyUrlParameterUsing = $callback;

        return $this;
    }
}

<?php

namespace SolutionForest\InspireCms\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\InspireCmsConfig;

class SetDefaultContentPageAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'setDefaultContentPage';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('inspirecms::buttons.set_default_content_page.label'));

        $this->icon(FilamentIcon::resolve('inspirecms::as_default'));

        $this->authorize('setAsDefault');

        $this->model(InspireCmsConfig::getContentModelClass());

        $this->hidden(function (array $arguments, ?Model $record) {

            if ($record) {
                return ! $record->isWebPage() || ($record->is_default == true) || $record->isLocked() || count($record->ancestorsAndSelf ?? []) > 1;
            }

            if (! isset($arguments['node']) && ! is_array($arguments['node'] ?? null)) {
                return true;
            }

            if (($arguments['node']['__content_document_type_cat'] ?? null) != 'web') {
                return true;
            }

            return ($arguments['node']['depth'] ?? 1) != 0;
        });

        $this->successNotificationTitle(__('inspirecms::buttons.set_default_content_page.messages.success.title'));

        $this->action(function (string $model, $itemKey, Action $action) {

            if (($record = $model::find($itemKey))) {

                $record->setAsDefault();

                $action->success();
            }
        });
    }
}

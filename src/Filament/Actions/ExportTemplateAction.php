<?php

namespace SolutionForest\InspireCms\Filament\Actions;

use Filament\Notifications\Notification;
use SolutionForest\InspireCms\Base\Filament\Actions\Concerns\CanCustomizeAuthorizedGuardActionProcess;
use SolutionForest\InspireCms\Filament\Contracts\GuardAction;
use SolutionForest\InspireCms\Support\MediaLibrary\Actions\Action;

class ExportTemplateAction extends Action implements GuardAction
{
    use CanCustomizeAuthorizedGuardActionProcess;

    public static function getDefaultName(): ?string
    {
        return 'export_templates';
    }

    public static function getPermissionName(): string
    {
        return 'action_export_templates';
    }

    public static function getPermissionDisplayName(): string
    {
        return __('inspirecms::actions.export_templates.permission_display_name');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('inspirecms::actions.export_templates.label'));

        $this->color('gray');

        $this->icon('heroicon-m-arrow-down-tray');

        $this->model(fn () => \SolutionForest\InspireCms\InspireCmsConfig::getTemplateModelClass());

        $this->successNotification(fn (Notification $notification) => $notification
            ->title(__('inspirecms::actions.export_templates.notification.success.title'))
            ->body(__('inspirecms::actions.export_templates.notification.success.body'))
        );

        $this->failureNotification(fn (Notification $notification) => $notification
            ->title(__('inspirecms::actions.export_templates.notification.failure.title'))
        );

        $this->action(function (?string $model, Action $action) {
            if (! $model) {
                return;
            }

            $templates = $model::all();

            $failedTemplates = [];

            foreach (array_keys(inspirecms_templates()->getAvailableThemes()) as $theme) {
                
                foreach ($templates as $template) {
                    try {
                        inspirecms_templates()->exportTemplate($template, $theme);
                    } catch (\Throwable $th) {
                        $failedTemplates[$theme][$template->getKey()] = $th->getMessage();
                        logger()->warning(
                            'Failed to export template',
                            [
                                'template' => $template->getKey(),
                                'theme' => $theme,
                                'error' => $th->getMessage(),
                            ]
                        );
                    }
                }
            }
            if (count($failedTemplates) > 0) {
                $action->failure();
            } else {
                $action->success();
            }
        });
    }
}

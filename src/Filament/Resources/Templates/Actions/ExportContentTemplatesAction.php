<?php

namespace SolutionForest\InspireCms\Filament\Resources\Templates\Actions;

use Filament\Actions\Action;
use Filament\Support\Facades\FilamentIcon;
use SolutionForest\InspireCms\InspireCmsConfig;
use Throwable;

class ExportContentTemplatesAction
{
    public static function make(): Action
    {
        return Action::make('exportContentTemplates')
            ->label(__('inspirecms::buttons.export_content_templates.label'))
            ->icon(FilamentIcon::resolve('inspirecms::export'))
            ->modalIcon(FilamentIcon::resolve('inspirecms::export'))
            ->color('gray')
            ->successNotificationTitle(__('inspirecms::buttons.export_content_templates.messages.success.title'))
            ->failureNotificationTitle(__('inspirecms::buttons.export_content_templates.messages.failure.title'))
            ->action(function (Action $action) {

                $exportResult = self::exportContentTemplates();

                if ($exportResult['success']) {
                    $action->success();
                } else {
                    $action->failure();
                }
            });
    }

    public static function exportContentTemplates()
    {
        $templates = InspireCmsConfig::getTemplateModelClass()::all();

        $failedTemplates = [];

        $themes = inspirecms_templates()->getAvailableThemes();

        foreach ($themes as $theme) {

            foreach ($templates as $template) {

                try {

                    inspirecms_templates()->exportTemplate($template, $theme);

                } catch (Throwable $th) {

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

        return [
            'success' => count($failedTemplates) <= 0,
            'failedTemplates' => $failedTemplates,
        ];
    }
}

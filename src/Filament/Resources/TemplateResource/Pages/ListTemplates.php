<?php

namespace SolutionForest\InspireCms\Filament\Resources\TemplateResource\Pages;

use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListRecords;
use SolutionForest\InspireCms\Filament\Resources\Helpers\TemplateResourceHelper;
use SolutionForest\InspireCms\Filament\Resources\TemplateResource;
use SolutionForest\InspireCms\Filament\Widgets\TemplateInfo;
use SolutionForest\InspireCms\Filament\Widgets\ThemeInfo;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Template;

class ListTemplates extends BaseListRecords
{
    public ?string $theme = null;

    public function mount(): void
    {
        parent::mount();

        $this->theme = inspirecms_templates()->getCurrentTheme();
    }

    public static function getResource(): string
    {
        return InspireCmsConfig::getFilamentResource('template', TemplateResource::class);
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->headerActions([
                Tables\Actions\SelectAction::make('theme')
                    ->options(TemplateResourceHelper::getThemeSelectOptions())
                    ->view('inspirecms::filament.actions.select-action', [
                        'icon' => FilamentIcon::resolve('inspirecms::theme'),
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->recordTitle(fn (Template $record) => $record->slug)
                    ->modalWidth('7xl')
                    ->slideOver()
                    ->beforeFormFilled(function (Model | Template $record, Tables\Actions\Action $action) {
                        try {
                            $record->initializeTemplate($this->theme);
                            $record->save();
                        } catch (\Throwable $th) {
                            Notification::make()
                                ->title(__('inspirecms::messages.something_went_wrong'))
                                ->body($th->getMessage())
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    })
                    ->form([
                        TemplateResourceHelper::getThemeFormComponent()->disabled(),
                        TemplateResourceHelper::getContentFormComponent()->disabled(),
                    ])
                    ->mutateRecordDataUsing(fn (Template $record) => [
                        'theme' => $this->theme,
                        'content' => $record->getContent(theme: $this->theme),
                    ]),
            ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ThemeInfo::class,
            TemplateInfo::class,
        ];
    }
}

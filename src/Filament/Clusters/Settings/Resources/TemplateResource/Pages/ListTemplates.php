<?php

namespace SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\TemplateResource\Pages;

use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListPage;
use SolutionForest\InspireCms\Filament\Clusters\Settings\Resources\TemplateResource;
use SolutionForest\InspireCms\Filament\Resources\Helpers\TemplateResourceHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Template;

class ListTemplates extends BaseListPage
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
                        'icon' => 'heroicon-o-paint-brush',
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
                                ->title(__('inspirecms::notification.something_went_wrong.title'))
                                ->body($th->getMessage())
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    })
                    ->form([
                        TemplateResourceHelper::getThemeFormComponent()->disabled(),
                        TemplateResourceHelper::getContentFormComponent(),
                    ])
                    ->mutateRecordDataUsing(fn (Template $record) => [
                        'theme' => $this->theme,
                        'content' => $record->getContent(theme: $this->theme),
                    ]),
            ]);
    }
}

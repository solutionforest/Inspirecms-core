<?php

namespace SolutionForest\InspireCms\Filament\Resources\Templates\Tables;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Resources\Templates\Actions\ThemeSelector;
use SolutionForest\InspireCms\Filament\Resources\Templates\Actions\ViewUsageAction;
use SolutionForest\InspireCms\Filament\Resources\Templates\Tables\Columns\TemplateSlugColumn;
use SolutionForest\InspireCms\Models\Contracts\Template;
use Throwable;

class TemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitle(fn ($record) => $record->slug)
            ->modelLabel(__('inspirecms::inspirecms.template.singular'))
            ->columns([
                TemplateSlugColumn::make(),
            ])
            ->headerActions([
                ThemeSelector::make(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalWidth('7xl')
                    ->slideOver()
                    ->beforeFormFilled(function (Model | Template $record, Action $action, $livewire) {
                        try {
                            $record->initializeTemplate($livewire->theme);
                            $record->save();
                        } catch (Throwable $th) {
                            Notification::make()
                                ->title(__('inspirecms::messages.something_went_wrong'))
                                ->body($th->getMessage())
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    })
                    ->mutateRecordDataUsing(fn (Template $record, $livewire) => [
                        'theme' => $livewire->theme,
                        'content' => $record->getContent(theme: $livewire->theme),
                    ]),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->visible(function (Model | Template $record) {
                        return count($record->documentTypes ?? []) <= 0 &&
                            count($record->contents ?? []) <= 0;
                    }),
                ViewUsageAction::make(),
            ]);
    }
}

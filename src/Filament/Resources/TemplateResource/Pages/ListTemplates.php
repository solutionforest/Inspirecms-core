<?php

namespace SolutionForest\InspireCms\Filament\Resources\TemplateResource\Pages;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use SolutionForest\InspireCms\Base\Filament\Resources\Pages\BaseListRecords;
use SolutionForest\InspireCms\Filament\Resources\ContentResource;
use SolutionForest\InspireCms\Filament\Resources\DocumentTypeResource;
use SolutionForest\InspireCms\Filament\Resources\Helpers\TemplateResourceHelper;
use SolutionForest\InspireCms\Filament\Resources\TemplateResource;
use SolutionForest\InspireCms\Filament\Widgets\TemplateInfo;
use SolutionForest\InspireCms\Filament\Widgets\ThemeInfo;
use SolutionForest\InspireCms\Helpers\FilamentResourceHelper;
use SolutionForest\InspireCms\Helpers\UIHelper;
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
            ->modifyQueryUsing(fn ($query) => $query->with(['documentTypes', 'contents' => fn ($query) => $query->withoutGlobalScopes([SoftDeletingScope::class])]))
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
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->visible(function (Model | Template $record) {
                        return count($record->documentTypes ?? []) <= 0 &&
                            count($record->contents ?? []) <= 0;
                    }),
                Tables\Actions\Action::make('viewUsage')
                    ->modalSubmitAction(fn () => false) // Disable the form submission
                    ->color('gray')
                    ->icon(FilamentIcon::resolve('actions::view-action') ?? 'heroicon-m-eye')
                    ->infolist(
                        fn (Infolist $infolist) => $infolist
                            ->schema([
                                TextEntry::make('id')
                                    ->label(__('inspirecms::inspirecms.id')),
                                TextEntry::make('slug')
                                    ->label(__('inspirecms::resources/template.slug.label'))
                                    ->badge(),
                                TextEntry::make('documentTypes')
                                    ->label(__('inspirecms::inspirecms.document_type'))
                                    ->getStateUsing(fn (Template | Model $record) => $record->documentTypes)
                                    ->formatStateUsing(function ($state) {
                                        if (! $state instanceof Model) {
                                            return __('inspirecms::inspirecms.n/a');
                                        }
                                        $url = FilamentResourceHelper::attemptToGetUrl(
                                            InspireCmsConfig::getFilamentResource('document_type', DocumentTypeResource::class),
                                            ['edit', 'view'],
                                            ['record' => $state],
                                            false
                                        );

                                        return UIHelper::generateLink($state->slug, $url, [
                                            'target' => '_blank',
                                        ]);
                                    })
                                    ->listWithLineBreaks(),
                                TextEntry::make('contents')
                                    ->label(__('inspirecms::inspirecms.content'))
                                    ->getStateUsing(fn (Template | Model $record) => $record->contents)
                                    ->formatStateUsing(function ($state) {
                                        if (! $state instanceof Model) {
                                            return __('inspirecms::inspirecms.n/a');
                                        }
                                        $url = FilamentResourceHelper::attemptToGetUrl(
                                            InspireCmsConfig::getFilamentResource('content', ContentResource::class),
                                            ['edit', 'view'],
                                            ['record' => $state],
                                            false
                                        );

                                        return UIHelper::generateLink($state->slug, $url, [
                                            'target' => '_blank',
                                        ]);
                                    })
                                    ->listWithLineBreaks(),
                            ])
                    ),
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

<?php

namespace SolutionForest\InspireCms\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use SolutionForest\InspireCms\Filament\Clusters\Settings;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionPageTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSectionPage;
use SolutionForest\InspireCms\Services\ImportDataServiceInterface;

class ImportData extends Page implements ClusterSectionPage
{
    use ClusterSectionPageTrait;

    /**
     * @var view-string
     */
    protected static string $view = 'inspirecms::filament.pages.import-data';

    protected static ?string $slug = 'import';

    protected static ?string $navigationIcon = 'heroicon-c-arrow-up-tray';

    protected static ?string $cluster = Settings::class;

    protected ?string $maxContentWidth = 'full';

    public array $data = [];

    protected ImportDataServiceInterface $importDataService;

    public function boot(ImportDataServiceInterface $importDataService): void
    {
        $this->importDataService = $importDataService;
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('file')
                        ->label(__('inspirecms::pages/import-data.steps.file.label'))
                        ->icon('heroicon-o-document-arrow-up')
                        ->schema([
                            Forms\Components\FileUpload::make('file')
                                ->label(__('inspirecms::pages/import-data.fields.file.label'))
                                ->hint(__('inspirecms::pages/import-data.fields.file.instructions'))
                                ->acceptedFileTypes(['application/json'])
                                ->storeFiles(false),
                        ]),
                ])
                    ->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                    <x-filament::button
                        type="submit"
                        size="md"
                        form="form"
                        hasFormProcessingLoadingIndicator="true"
                    >
                        {{ __('inspirecms::pages/import-data.actions.import.label') }}
                    </x-filament::button>
                BLADE))),
            ]);
    }

    public function import()
    {
        $file = data_get($this->form->getState(), 'file');

        if (is_null($file) || ! $file instanceof TemporaryUploadedFile || $file->getMimeType() !== 'application/json') {
            return;
        }

        $data = $this->importDataService->importFromFile($file);

        if (! $this->importDataService->validateBeforeRun()) {
            $this->getValidationErrorNotification($this->importDataService->getValidationErrors())->send();
            $this->resetAll();

            return;
        }

        try {

            $this->importDataService->run();

            if ($this->importDataService->hasErrors()) {

                $this->getErrorNotificationAfterProcess($this->importDataService->getErrors())->send();

            } else {

                $this->getSuccessNotification()->send();
            }

        } catch (\Throwable $th) {

            $this->getErrorNotification()->send();

        } finally {
            $this->resetAll();
        }
    }

    protected function refreshForm()
    {
        $this->form->fill([]);
    }

    protected function resetAll()
    {
        $this->importDataService->reset();
        $this->refreshForm();
        $this->redirectIntended($this->getUrl());
    }

    protected function getSuccessNotification(): Notification
    {
        return Notification::make()
            ->success()
            ->title(__('inspirecms::pages/import-data.notification.success.title'))
            ->body(__('inspirecms::pages/import-data.notification.success.message'));
    }

    protected function getErrorNotification(): Notification
    {
        return Notification::make()
            ->danger()
            ->title(__('inspirecms::pages/import-data.notification.error.title'))
            ->body(__('inspirecms::pages/import-data.notification.error.message'));
    }

    protected function getValidationErrorNotification($errors): Notification
    {
        $errors = collect($errors)->map(function ($typeErrors, $type) {
            $html = "<strong>{$type}</strong><br/>";

            if (is_array($typeErrors)) {
                $html .= '<ul>';
                $html .= collect($typeErrors)->map(fn ($error) => "<li>{$error}</li>")->implode('');
                $html .= '</ul>';
            } else {
                $html .= ": {$typeErrors}";
            }

            return $html;
        })->implode('');

        return Notification::make()
            ->danger()
            ->title(__('inspirecms::pages/import-data.notification.validation.title'))
            ->body($errors)
            ->seconds(30);
    }

    protected function getErrorNotificationAfterProcess($errors): Notification
    {
        $errors = collect($errors)->except('__validation__')->flatten()->map(function ($error) {
            return "<li>{$error}</li>";
        })->implode('');

        return Notification::make()
            ->danger()
            ->title(__('inspirecms::pages/import-data.notification.error-after-process.title'))
            ->body($errors)
            ->seconds(30);
    }

    public function getTitle(): string | Htmlable
    {
        return static::getNavigationLabel();
    }

    public static function getNavigationLabel(): string
    {
        return __('inspirecms::pages/import-data.title');
    }
}

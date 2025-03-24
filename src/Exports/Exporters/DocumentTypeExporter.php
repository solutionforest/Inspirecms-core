<?php

namespace SolutionForest\InspireCms\Exports\Exporters;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Exports\ExportResult;
use SolutionForest\InspireCms\Helpers\ImportDataHelper;
use SolutionForest\InspireCms\InspireCmsConfig;

class DocumentTypeExporter extends BaseImportUsedDataExporter
{
    public static function getArgsFormFields(): array
    {
        $records = collect(static::getModel()::all())->mapWithKeys(fn ($record) => [$record->getKey() => $record]);
        $options = $records->map(fn ($record) => $record->title)->all();
        $descriptions = $records->map(fn ($record) => $record->slug)->all();

        return [
            Toggle::make('with_content')
                ->label('With Content')
                ->hint('Export content records along with document types')
                ->default(true),
            Section::make('Filter Records')
                ->collapsible()
                ->statePath('filter_records')
                ->compact()
                ->schema([
                    CheckboxList::make('document_type')
                        ->label(__('inspirecms::inspirecms.document_type'))
                        ->hint('Keep empty to export all records.')
                        ->gridDirection('row')
                        ->columns(3)
                        ->searchable()
                        ->options($options)
                        ->descriptions($descriptions),
                ]),
        ];
    }

    public function export()
    {
        [$folderName, $fs, $fullPath, $subFolders] = $this->ensureTempFolderForExport('export-document-types', [
            ImportDataHelper::FOLDER_IDENTIFIER_DOCUMENTTYPE,
            ImportDataHelper::FOLDER_IDENTIFIER_FIELDGROUP,
            ImportDataHelper::FOLDER_IDENTIFIER_TEMPLATE,
            ImportDataHelper::FOLDER_IDENTIFIER_CONTENT,
        ]);

        [$records, $perPage, $page] = $this->getRecordsToExport();
        $errors = [];

        foreach ($records->items() as $record) {

            $this->processRecordForImportUsed(
                $record,
                $fs,
                (Arr::get($subFolders, ImportDataHelper::FOLDER_IDENTIFIER_DOCUMENTTYPE) ?? $folderName),
                $errors,
            );

            foreach ($record->fieldGroups as $fieldGroup) {

                $this->processRecordForImportUsed(
                    $fieldGroup,
                    $fs,
                    (Arr::get($subFolders, ImportDataHelper::FOLDER_IDENTIFIER_FIELDGROUP) ?? $folderName),
                    $errors,
                );
            }

            foreach ($record->templates as $template) {

                $this->processRecordForImportUsed(
                    $template,
                    $fs,
                    (Arr::get($subFolders, ImportDataHelper::FOLDER_IDENTIFIER_TEMPLATE) ?? $folderName),
                    $errors,
                );
            }

            if ($this->record->getArgsForExporter()['with_content'] ?? false) {

                foreach ($record->content as $content) {

                    $this->processRecordForImportUsed(
                        $content,
                        $fs,
                        (Arr::get($subFolders, ImportDataHelper::FOLDER_IDENTIFIER_CONTENT) ?? $folderName),
                        $errors,
                    );
                }
            }

        }

        $processingErrors = array_merge(
            $this->record->getProcessingMessages()['errors'] ?? [],
            $errors,
        );

        if ($page >= $records->lastPage()) {
            return $this->handleExportCompletion($folderName, $processingErrors);
        }

        return ExportResult::paused(
            $this->buildProcessingData($page, $perPage, $processingErrors, $folderName),
        );
    }

    /**
     * @return array{0: \Illuminate\Pagination\LengthAwarePaginator, 1: int, 2: int}
     */
    private function getRecordsToExport()
    {
        $processingData = $this->record->getProcessingMessages();
        $perPage = $processingData['perPage'] ?? 100;
        $page = $processingData['page'] ?? 1;

        $args = $this->record->getArgsForExporter();

        $relations = [
            'fieldGroups',
            'templates',
            'allowedDocumentTypes',
            'content',
        ];

        if ($args['with_content'] ?? false) {
            $relations[] = 'content.parent.path';
            $relations[] = 'content.sitemap';
            $relations[] = 'content.webSetting';
            $relations[] = 'content.documentType';
        }

        $query = static::getModel()::query()->with($relations);

        if (isset($args['filter_records']['document_type']) && ! empty($args['filter_records']['document_type'])) {
            $query->whereKey($args['filter_records']['document_type']);
        }

        $records = $query->paginate(perPage: $perPage, page: $page);

        return [$records, $perPage, $page];
    }

    private static function getModel()
    {
        return InspireCmsConfig::getDocumentTypeModelClass();
    }
}

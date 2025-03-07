<?php

namespace SolutionForest\InspireCms\Exports\Exporters;

use Filament\Forms\Components\CheckboxList;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Exports\ExportResult;
use SolutionForest\InspireCms\Helpers\ImportDataHelper;
use SolutionForest\InspireCms\InspireCmsConfig;

class DocumentTypeExporter extends BaseExporter
{
    public static function getArgsFormFields(): array
    {
        $records = collect(static::getModel()::all())->mapWithKeys(fn ($record) => [$record->getKey() => $record]);
        $options = $records->map(fn ($record) => $record->title)->all();
        $descriptions = $records->map(fn ($record) => $record->slug)->all();

        return [
            CheckboxList::make('filter_record')
                ->label('Filter Records')
                ->hint('Keep empty to export all records.')
                ->columns(3)
                ->searchable()
                ->options($options)
                ->descriptions($descriptions),
        ];
    }

    public function export()
    {
        [$folderName, $fs, $fullPath, $subFolders] = $this->ensureTempFolderForExport('export-document-types', [
            ImportDataHelper::FOLDER_IDENTIFIER_DOCUMENTTYPE,
        ]);

        [$records, $perPage, $page] = $this->getDocumentTypeRecords();
        $errors = [];

        foreach ($records->items() as $record) {

            $this->processRecordForImportUsed(
                $record,
                $fs,
                (Arr::first($subFolders) ?? $folderName),
                $errors,
            );
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
    private function getDocumentTypeRecords()
    {
        $processingData = $this->record->getProcessingMessages();
        $perPage = $processingData['perPage'] ?? 100;
        $page = $processingData['page'] ?? 1;

        $args = $this->record->getArgsForExporter();

        $query = static::getModel()::query()->with(['fieldGroups', 'templates', 'rejectedDocumentTypes']);

        if (! empty($args['filter_record'])) {
            $query->whereKey($args['filter_record']);
        }

        $records = $query->paginate(perPage: $perPage, page: $page);

        return [$records, $perPage, $page];
    }

    private static function getModel()
    {
        return InspireCmsConfig::getDocumentTypeModelClass();
    }
}

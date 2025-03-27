<?php

namespace SolutionForest\InspireCms\Exports\Exporters;

use Filament\Forms\Components\CheckboxList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Exports\ExportResult;
use SolutionForest\InspireCms\Helpers\ImportDataHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts as ModelContracts;

class ImportUsedExporter extends BaseImportUsedDataExporter
{
    public static function getArgsFormFields(): array
    {
        return [
            CheckboxList::make('export_types')
                ->inlineLabel()
                ->options(static::getExportTypeOptions())
                ->columnSpanFull()
                ->columns(3)
                ->gridDirection('row')
                ->live()
                ->bulkToggleable()
                ->required(),
        ];
    }

    public function export()
    {
        [$currentExportingFolder, $targetFolders, $records, $perPage, $page] = $this->getRecordsToExport();

        // Avoid loop
        if (blank($currentExportingFolder)) {
            return ExportResult::failed('No export folder selected. Please select at least one export type.');
        }
        
        [$folderName, $fs, $fullPath, $subFolders] = $this->ensureTempFolderForExport('import_used', $targetFolders);

        $errors = [];

        foreach ($records->items() as $record) {
            
            $this->processRecordForImportUsed(
                $record,
                $fs,
                (Arr::get($subFolders, $currentExportingFolder) ?? $folderName),
                $errors,
            );
        }

        $processingData = $this->record->getProcessingMessages();
        $processingErrors = array_merge($processingData['errors'] ?? [], $errors);

        $isCurrentStepFinished = $page >= $records->lastPage();
        $isLastFolder = Arr::last($targetFolders) == $currentExportingFolder;

        // All folders and records exported
        if ($isLastFolder && $isCurrentStepFinished) {
            return $this->handleExportCompletion($folderName, $processingErrors);
        }
        
        $processingData['folderName'] = $folderName;
        $processingData['errors'] = $processingErrors;
        $processingData['__steps'][$currentExportingFolder]['nextPage'] = $page + 1;
        $processingData['__steps'][$currentExportingFolder]['perPage'] = $perPage;

        if ($isCurrentStepFinished) {
            // Get next step
            $currentIndex = array_search($currentExportingFolder, $targetFolders);
            $nextIndex = $currentIndex !== false ? $currentIndex + 1 : 0;
            
            if ($nextIndex < count($targetFolders)) {
                $processingData['nextStep'] = $targetFolders[$nextIndex];
            } else {
                $processingData['nextStep'] = Arr::last($targetFolders);
            }

        } else {
            $processingData['nextStep'] = $currentExportingFolder;
        }

        return ExportResult::paused($processingData);
    }

    /**
     * @return array{0:string,1:array,2:\Illuminate\Pagination\LengthAwarePaginator,3:int,4:int}
     */
    private function getRecordsToExport()
    {
        $processingData = $this->record->getProcessingMessages();

        $args = $this->record->getArgsForExporter();
        $folders = Arr::wrap($args['export_types'] ?? []);
        $currentExportType = ($processingData['nextStep'] ?? null) ?? Arr::first($folders);

        $page = $processingData['__steps'][$currentExportType]['nextPage'] ?? 1;
        $perPage = $processingData['__steps'][$currentExportType]['perPage'] ?? 500;

        $query = $this->buildExportingQueryForImportUsed(
            static::getBaseQueryForModel($currentExportType),
            $currentExportType,
        );

        if ($currentExportType == ImportDataHelper::FOLDER_IDENTIFIER_NAVIGATION && $query->getModel() instanceof ModelContracts\Navigation) {

            $allRecord = $query->get();

            $tree = $allRecord->toTree();
            $totalTreeItems = count($tree);

            $perPage = $totalTreeItems;
            $page = 1;

            // Create a paginator for the tree structure
            $records = new \Illuminate\Pagination\LengthAwarePaginator(
                items: $tree,
                total: $totalTreeItems,
                perPage: $perPage,
                currentPage: $page,
            );

        } else {
            $records = $query->paginate(perPage: $perPage, page: $page);
        }

        return [
            $currentExportType,
            $folders,
            $records,
            $perPage,
            $page,
        ];
    }

    /** @return string[] */
    protected static function getExportTypeOptions()
    {
        return collect(ImportDataHelper::FOLDER_STRUCTURE)
            ->mapWithKeys(fn ($folder) => [
                $folder => str($folder)->snake()->replace('_', ' ')->apa()->toString(),
            ])
            ->except(ImportDataHelper::FOLDER_IDENTIFIER_VIEW)
            ->all();
    }

    protected static function getFilterRecordKeyFor(string $type)
    {
        $prefix = match ($type) {
            ImportDataHelper::FOLDER_IDENTIFIER_DOCUMENTTYPE => 'document_type',
            ImportDataHelper::FOLDER_IDENTIFIER_FIELDGROUP => 'field_group',
            ImportDataHelper::FOLDER_IDENTIFIER_TEMPLATE => 'template',
            ImportDataHelper::FOLDER_IDENTIFIER_CONTENT => 'content',
            ImportDataHelper::FOLDER_IDENTIFIER_NAVIGATION => 'navigation',
            default => str($type)->snake()->toString(),
        };

        return "{$prefix}_records";
    }

    protected static function getFilterRecordsFor(string $type)
    {
        $query = static::getBaseQueryForModel($type);

        if (!$query) {
            return collect();
        }

        return $query->get();
    }

    /**
     * @param string $type
     * @param Collection<Model> $records
     * @return array
     */
    private static function buildOptionsFor(string $type, $records)
    {
        $results = [];

        foreach ($records as $record) {

            $key = $record->getKey();

            $label = null;

            if ($record instanceof ModelContracts\DocumentType) {
                $label = $record->title;
            } elseif ($record instanceof ModelContracts\FieldGroup) {
                $label = $record->title;
            } elseif ($record instanceof ModelContracts\Template) {
                $label = $record->slug;
            } elseif ($record instanceof ModelContracts\Content) {
                $label = $record->title;
            } elseif ($record instanceof ModelContracts\Navigation) {
                $label = "{$record->title} ({$record->type})";
            } 
            
            $results[$key] = $label ?? $key;

        }

        return $results;
    }
    /**
     * @param string $type
     * @param Collection<Model> $records
     * @return array
     */
    private static function buildOptionDescriptionsFor(string $type, $records)
    {
        $results = [];

        foreach ($records as $record) {

            $key = $record->getKey();

            $label = null;

            if ($record instanceof ModelContracts\DocumentType) {
                $label = $record->slug;
            } elseif ($record instanceof ModelContracts\FieldGroup) {
                $label = $record->name;
            } elseif ($record instanceof ModelContracts\Template) {
                $label = $record->slug;
            } elseif ($record instanceof ModelContracts\Content) {
                $label = $record->slug;
            } elseif ($record instanceof ModelContracts\Navigation) {
                $label = "{$record->category}";
            } 
            
            $results[$key] = $label ?? $key;

        }

        return $results;
    }

    /**
     * @return ?\Illuminate\Database\Eloquent\Builder
     */
    protected static function getBaseQueryForModel(string $type)
    {
        $model = match ($type) {
            ImportDataHelper::FOLDER_IDENTIFIER_DOCUMENTTYPE => InspireCmsConfig::getDocumentTypeModelClass(),
            ImportDataHelper::FOLDER_IDENTIFIER_FIELDGROUP => InspireCmsConfig::getFieldGroupModelClass(),
            ImportDataHelper::FOLDER_IDENTIFIER_TEMPLATE => InspireCmsConfig::getTemplateModelClass(),
            ImportDataHelper::FOLDER_IDENTIFIER_CONTENT => InspireCmsConfig::getContentModelClass(),
            ImportDataHelper::FOLDER_IDENTIFIER_NAVIGATION => InspireCmsConfig::getNavigationModelClass(),
            default => null,
        };

        if ($model && is_a($model, Model::class, true)) {
            return $model::query();
        }

        return null;
    }
}

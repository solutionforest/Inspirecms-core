<?php

namespace SolutionForest\InspireCms\Exports\Exporters;

use Filament\Forms\Components\CheckboxList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use SolutionForest\InspireCms\Exports\ExportResult;
use SolutionForest\InspireCms\Helpers\ImportDataHelper;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Models\Contracts\DocumentType;
use SolutionForest\InspireCms\Models\Contracts\FieldGroup;
use SolutionForest\InspireCms\Models\Contracts\Navigation;
use SolutionForest\InspireCms\Models\Contracts\Template;

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

        if ($currentExportingFolder === ImportDataHelper::FOLDER_IDENTIFIER_NAVIGATION) {

            $this->processNavigationRecordsForImportUsed(
                $records->items(),
                $fs,
                (Arr::get($subFolders, ImportDataHelper::FOLDER_IDENTIFIER_NAVIGATION) ?? $folderName),
                $errors,
            );

        } else {

            collect($records->items())->each(fn ($record) => $this->processRecordForImportUsed(
                $record,
                $fs,
                (Arr::get($subFolders, $currentExportingFolder) ?? $folderName),
                $errors,
            ));
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

        if ($currentExportType == ImportDataHelper::FOLDER_IDENTIFIER_NAVIGATION && $query->getModel() instanceof Navigation) {

            $allRecord = $query->get();

            $tree = $allRecord->groupBy('category')->map(fn ($r) => $r->toTree());
            $totalTreeItems = count($tree);

            $perPage = $totalTreeItems;
            $page = 1;

            // Create a paginator for the tree structure
            $records = new LengthAwarePaginator(
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

        if (! $query) {
            return collect();
        }

        return $query->get();
    }

    /**
     * @param  Collection<Model>  $records
     * @return array
     */
    private static function buildOptionsFor(string $type, $records)
    {
        $results = [];

        foreach ($records as $record) {

            $key = $record->getKey();

            $label = null;

            if ($record instanceof DocumentType) {
                $label = $record->title;
            } elseif ($record instanceof FieldGroup) {
                $label = $record->title;
            } elseif ($record instanceof Template) {
                $label = $record->slug;
            } elseif ($record instanceof Content) {
                $label = $record->title;
            } elseif ($record instanceof Navigation) {
                $label = "{$record->title} ({$record->type})";
            }

            $results[$key] = $label ?? $key;

        }

        return $results;
    }

    /**
     * @param  Collection<Model>  $records
     * @return array
     */
    private static function buildOptionDescriptionsFor(string $type, $records)
    {
        $results = [];

        foreach ($records as $record) {

            $key = $record->getKey();

            $label = null;

            if ($record instanceof DocumentType) {
                $label = $record->slug;
            } elseif ($record instanceof FieldGroup) {
                $label = $record->name;
            } elseif ($record instanceof Template) {
                $label = $record->slug;
            } elseif ($record instanceof Content) {
                $label = $record->slug;
            } elseif ($record instanceof Navigation) {
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
            ImportDataHelper::FOLDER_IDENTIFIER_LANGUAGE => InspireCmsConfig::getLanguageModelClass(),
            default => null,
        };

        if ($model && is_a($model, Model::class, true)) {
            return $model::query();
        }

        return null;
    }

    protected function processNavigationRecordsForImportUsed(
        $records,
        $fs,
        ?string $dir,
        array &$errors,
    ) {
        try {
            foreach ($records as $key => $record) {
                // Already grouped by category, $key = category
                if (! $record instanceof Model) {
                    foreach ($record as $index => $modelNavigation) {
                        try {

                            $filename = "{$key}_{$index}.json";


                            $content = $this->prepareImportContentFromModel($modelNavigation);

                            $path = $dir . '/' . $filename;
                            $fs->put($path, $content);

                        } catch (\Throwable $th) {
                            $errors[] = [
                                'record' => $record->getKey(),
                                'model' => get_class($record),
                                'message' => $th->getMessage(),
                            ];
                        }

                    }
                } else {
                    $this->processRecordForImportUsed($record, $fs, $dir, $errors);
                }
            }
        } catch (\Throwable $th) {
            $errors[] = [
                'record' => null,
                'model' => Navigation::class,
                'message' => $th->getMessage(),
            ];
        }
    }
}

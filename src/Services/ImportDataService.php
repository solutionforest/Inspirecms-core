<?php

namespace SolutionForest\InspireCms\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Helpers\ModelHelper;
use SolutionForest\InspireCms\ImportData\Entities;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Models\Contracts\DocumentType;
use SolutionForest\InspireCms\Models\Contracts\FieldGroup;
use SolutionForest\InspireCms\Models\Contracts\Template;

class ImportDataService implements ImportDataServiceInterface
{
    /**
     * @var array{documentTypes: array<string,Entities\DocumentType>, fieldGroups: array<string,Entities\FieldGroup>, templates: array<string,Entities\Template>, fields: array<string,Entities\Field>, content: array<string,Entities\Content>, navigation: array<string,Entities\Navigation>}
     */
    protected array $pendingData = [];

    /**
     * @var array
     *
     * An array to keep track of the finished import data processes.
     */
    protected array $finished = [];

    protected ?string $nextProcess = null;

    /**
     * @var array
     *
     * An array to store errors encountered during the data import process.
     */
    protected array $processErrors = [];

    /**
     * @var array
     *
     * An array to temporarily store models during the import process.
     */
    protected array $tempModels = [];

    const PROCESS_ORDER = [
        'templates',
        'fieldGroups',
        'documentTypes',
        'fields',
        'content',
        'navigation',
    ];

    public function __construct(
        protected ContentServiceInterface $contentService,
    ) {}

    /** {@inheritDoc} */
    public function addDocumentType(string $slug, Entities\DocumentType $data)
    {
        if (isset($this->pendingData['documentTypes'][$slug])) {
            return;
        }
        $data->slug = $slug;
        $this->pendingData['documentTypes'][$slug] = $data;
    }

    /** {@inheritDoc} */
    public function addFieldGroup(string $slug, Entities\FieldGroup $data, array $fields)
    {
        if (isset($this->pendingData['fieldGroups'][$slug])) {
            return;
        }

        $data->slug = $slug;
        $this->pendingData['fieldGroups'][$slug] = $data;

        foreach ($fields as $item) {

            $fieldKey = $data->slug . '.' . $item->slug;

            if (isset($this->pendingData['fields'][$fieldKey])) {
                continue;
            }

            $this->pendingData['fields'][$fieldKey] = $item;
        }
    }

    /** {@inheritDoc} */
    public function addTemplate(string $slug, Entities\Template $data)
    {
        if (isset($this->pendingData['templates'][$slug])) {
            return;
        }

        $data->slug = $slug;
        $this->pendingData['templates'][$slug] = $data;
    }

    /** {@inheritDoc} */
    public function addContent(string $slug, ?string $parent, Entities\Content $data)
    {
        $contentKey = ($parent ?? '__root__') . '/' . $slug;

        if (isset($this->pendingData['content'][$contentKey])) {
            return;
        }

        $data->slug = $slug;
        $this->pendingData['content'][$contentKey] = $data;
    }

    /** {@inheritDoc} */
    public function addNavigation(Entities\Navigation $data)
    {
        $this->pendingData['navigation'][] = $data;
    }

    /** {@inheritDoc} */
    public function run()
    {
        if ($this->isAllDone()) {
            return;
        }

        $this->initProcess();

        try {
            while ($this->haveNextProcess()) {

                $process = $this->getNextProcess();

                $this->runProcess($process);
            }
        } catch (\Throwable $th) {
            $this->processErrors['__process__']['__error__'] = $th->getMessage();
        }
    }

    /** {@inheritDoc} */
    public function reset()
    {
        $this->pendingData = [];
        $this->finished = [];
        $this->resetTempModels();
        $this->resetProcess();
    }

    /** {@inheritDoc} */
    public function validateBeforeRun(): bool
    {
        if (empty($this->pendingData)) {
            $this->processErrors['__process__']['__error__'] = 'No data to import.';

            return false;
        }

        foreach ($this->pendingData as $type => $items) {
            foreach ($items as $item) {
                try {
                    $item->validate();
                } catch (\Throwable $th) {
                    $this->processErrors['__validation__'][$type][] = $th->getMessage();
                }
            }
        }

        return empty($this->processErrors['__validation__']);
    }

    /** {@inheritDoc} */
    public function hasErrors(): bool
    {
        return ! empty($this->processErrors);
    }

    /** {@inheritDoc} */
    public function getErrors(): array
    {
        return $this->processErrors;
    }

    /** {@inheritDoc} */
    public function getValidationErrors(): array
    {
        return $this->processErrors['__validation__'] ?? [];
    }

    protected function processForTemplates()
    {
        $model = InspireCmsConfig::getTemplateModelClass();

        $this->guardAgaintsTableExist($model);

        foreach ($this->pendingData['templates'] ?? [] as $slug => $item) {

            try {

                $item->validate();

                $template = $this->findTemplates($slug)->first();

                if (! $template) {
                    $template = new $model($item->getDataForModel());
                    if (filled($item->content)) {
                        $template->preloadTemplateContentBeforeCreate($item->content);
                    }
                    $template->save();
                    $template->refresh();
                }

                $this->finished['templates'][$slug] = $template;

            } catch (\Throwable $e) {
                $this->processErrors['templates'][$slug] = $e->getMessage();
            }
        }
    }

    protected function processForFieldGroups()
    {
        $model = InspireCmsConfig::getFieldGroupModelClass();

        $this->guardAgaintsTableExist($model);

        foreach ($this->pendingData['fieldGroups'] ?? [] as $name => $item) {
            try {

                $item->validate();

                $fieldGroup = $this->findFieldGroups($name)->first();

                if (! $fieldGroup) {
                    $fieldGroup = $model::create($item->getDataForModel());
                }

                $this->finished['fieldGroups'][$name] = $fieldGroup;

            } catch (\Throwable $th) {
                $this->processErrors['fieldGroups'][$name] = $th->getMessage();
            }
        }
    }

    protected function processForDocumentTypes()
    {
        $model = InspireCmsConfig::getDocumentTypeModelClass();

        $this->guardAgaintsTableExist($model);

        $reorderDocumentTypes = function ($collection) {
            $higherOrder = $collection->filter(
                fn ($i) => ! (is_array($i->inheritance) && count($i->inheritance ?? []) > 0) &&
                empty($i->parent)
            );

            $noParent = $collection->filter(fn ($i) => empty($i->parent));
            $withParent = $collection->filter(fn ($i) => ! empty($i->parent));

            // Sort the document types so that parents are created before children
            $noParentKeys = $noParent->keys()->all();
            $withParentKeys = $withParent->keys()->all();
            $withParentOrder = $withParent->map(function ($i) use ($noParentKeys, $withParentKeys) {
                if (in_array($i->parent, $noParentKeys)) {
                    return 0;
                }
                if (in_array($i->parent, $withParentKeys)) {
                    return 2;
                }

                return 1;
            })->all();

            return $collection->sortBy(function ($i) use ($higherOrder, $withParentOrder) {
                // Higher Order
                if ($higherOrder->has($i->slug)) {
                    return -1;
                }
                // With Parent Order
                if (array_key_exists($i->slug, $withParentOrder)) {
                    return $withParentOrder[$i->slug] + 1;
                }

                // Default Order
                return 0;
            });
        };

        $this->pendingData['documentTypes'] = $reorderDocumentTypes(collect($this->pendingData['documentTypes'] ?? []))->toArray();

        foreach ($this->pendingData['documentTypes'] as $slug => $item) {

            try {

                $item->validate();

                $documentTypeData = $item->getDataForModel();
                $documentType = $this->findDocumentTypes($slug)->first();

                if (! $documentType) {
                    $documentType = $model::create($documentTypeData);
                }

                if (filled($item->parent)) {

                    $parentDocumentType = $this->findDocumentTypes($item->parent)->first();

                    if (! $parentDocumentType) {
                        throw new \Exception("Parent document type '{$item->parent}' not found.");
                    }

                    $documentType->parent()->associate($parentDocumentType);
                    $documentType->save();
                }

                if (! empty($item->fieldGroups)) {
                    $fieldGroupKeys = $this->findFieldGroups($item->fieldGroups)->map(fn ($i) => $i->getKey())->filter()->values();
                    $documentType->fieldGroups()->sync($fieldGroupKeys);
                }

                if (! empty($item->templates)) {
                    $templateKeys = $this->findTemplates($item->templates)->map(fn ($i) => $i->getKey())->filter()->values();
                    $documentType->templates()->sync($templateKeys);
                }

                if (filled($item->defaultTemplate)) {
                    $defaultTemplate = $this->findTemplates($item->defaultTemplate)->first();
                    if (! $defaultTemplate) {
                        throw new \Exception("Default template '{$item->defaultTemplate}' not found.");
                    }
                    $documentType->setAsDefaultTemplate($defaultTemplate->getKey());
                }

                foreach ($item->inheritance ?? [] as $inheritance) {
                    $inheritanceDocumentType = $this->findDocumentTypes($inheritance)->first();
                    if (! $inheritanceDocumentType) {
                        throw new \Exception("Inheritance document type '{$inheritance}' not found.");
                    }
                    $documentType->inheritDocumentType($inheritanceDocumentType);
                }

                $this->finished['documentTypes'][$slug] = $documentType;

            } catch (\Throwable $th) {
                $this->processErrors['documentTypes'][$slug] = $th->getMessage();
            }
        }
    }

    protected function processForFields()
    {
        $model = InspireCmsConfig::getFieldModelClass();

        $this->guardAgaintsTableExist($model);

        foreach ($this->pendingData['fields'] ?? [] as $fieldKey => $item) {

            try {

                $item->validate();

                [$group, $name] = explode('.', $fieldKey);

                $fieldGroup = $this->findFieldGroups($group)->first();

                if (! $fieldGroup) {
                    throw new \Exception("Field group {$group} does not exist.");
                }

                $field = $fieldGroup->fields()->where('name', $name)->first();

                if (! $field) {
                    $data = $this->mutateFieldData($item->getDataForModel());
                    $field = $fieldGroup->fields()->create($data);
                }

                $this->finished['fields'][$fieldKey] = $field;

            } catch (\Throwable $th) {
                $this->processErrors['fields'][$fieldKey] = $th->getMessage();
            }
        }
    }

    protected function processForContent()
    {
        $model = InspireCmsConfig::getContentModelClass();

        $this->guardAgaintsTableExist($model);

        foreach ($this->pendingData['content'] ?? [] as $contentKey => $item) {

            try {

                $item->validate();

                [$parentSlug, $slug] = [Str::beforeLast($contentKey, '/'), Str::afterLast($contentKey, '/')];

                $parent = $parentSlug === '__root__' ? null : $this->findContent($parentSlug)->first();

                $documentType = $this->findDocumentTypes($item->documentType)->first();

                if (! $documentType) {
                    throw new \Exception("Document type '{$item->documentType}' not found.");
                }

                $contentData = $item->getDataForModel();
                $contentData['document_type_id'] = $documentType->getKey();
                $contentData['parent_id'] = $parent?->getKey();

                $content = $model::where('slug', $slug)
                    ->when($parent, fn ($q) => $q->whereParent($parent->getKey()), fn ($q) => $q->isRoot())
                    ->first();

                if (! $content) {
                    $content = new $model($contentData);
                    $content->propertyData = json_encode($item->properties);
                    $content->setPublishableState($item->publishState);
                    $content->save();
                    $content->refresh();
                }

                $content->webSetting()->updateOrCreate([], $item->getWebSettingData());
                $content->sitemap()->updateOrCreate([], $item->getSitemapData());

                if (filled($item->template)) {
                    $template = $this->findTemplates($item->template)->first();
                    if (! $template) {
                        throw new \Exception("Template '{$item->template}' not found.");
                    }
                    $content->templates()->sync([$template->getKey()]);
                    $content->setAsDefaultTemplate($template);
                }

                $this->finished['content'][$contentKey] = $content;

            } catch (\Throwable $th) {
                $this->processErrors['content'][$contentKey] = $th->getMessage();
            }
        }
    }

    protected function processForNavigation()
    {
        $model = InspireCmsConfig::getNavigationModelClass();

        $this->guardAgaintsTableExist($model);

        foreach ($this->pendingData['navigation'] ?? [] as $item) {
            try {

                $item->validate();

                $navigationData = $this->mutateNavigationData($item);

                if (isset($item->id)) {
                    $navigation = $model::find($item->id);
                    if ($navigation) {
                        $navigation->update($navigationData);
                        $navigation->refresh();
                    } else {
                        continue;
                    }
                } else {
                    $navigation = $model::create($navigationData);
                }

                if (isset($navigation)) { 
                    $this->finished['navigation'][] = $navigation ?? null;
                }

            } catch (\Throwable $th) {
                $this->processErrors['navigation'][] = $th->getMessage();
            }
        }
    }

    /**
     * Executes the specified process.
     *
     * @param  string  $process  The name or identifier of the process to run.
     * @return void
     */
    protected function runProcess(string $process)
    {
        if (! $this->isWaitingFor($process)) {
            throw new \Exception('Invalid process.');
        }

        $method = (string) str($process)->studly()->prepend('processFor');

        if (! method_exists($this, $method)) {
            throw new \Exception("Method '{$method}' not found.");
        }

        $this->guardAgainstProcess($process);

        try {

            $this->{$method}();

        } catch (\Throwable $th) {

            $this->processErrors['__process__'][$process] = $th->getMessage();

        } finally {

            $this->setNextProcessFor($process);
        }
    }

    protected function initProcess()
    {
        if ($this->nextProcess || $this->isAllDone()) {
            return;
        }
        $this->nextProcess = static::PROCESS_ORDER[0] ?? null;
    }

    /**
     * Checks if the specified process is currently waiting.
     *
     * @param  string  $process  The name of the process to check.
     * @return bool Returns true if the process is waiting, false otherwise.
     */
    protected function isWaitingFor(string $process)
    {
        $this->guardAgainstProcess($process);

        return $this->nextProcess === $process;
    }

    /**
     * Checks if all tasks or processes are completed.
     *
     * @return bool Returns true if all tasks are done, otherwise false.
     */
    protected function isAllDone()
    {
        return $this->nextProcess === '__done__';
    }

    /**
     * Sets the next process to be executed.
     *
     * @param  string  $process  The name of the next process.
     * @return void
     */
    protected function setNextProcessFor(string $process)
    {
        $this->guardAgainstProcess($process);

        $all = static::PROCESS_ORDER;

        $key = array_search($process, $all);

        if ($key === false) {
            throw new \Exception('Invalid process.');
        }

        $next = $all[$key + 1] ?? '__done__';

        $this->nextProcess = $next;
    }

    /**
     * Determine if there is a next process to be executed.
     *
     * @return bool True if there is a next process, false otherwise.
     */
    protected function haveNextProcess()
    {
        return $this->nextProcess !== '__done__';
    }

    /**
     * Retrieve the next process to be executed.
     *
     * This method determines and returns the next process that should be
     * executed in the import data service workflow.
     *
     * @return ?string The next process to be executed.
     */
    protected function getNextProcess()
    {
        return $this->nextProcess;
    }

    /**
     * Initialize the data import process.
     *
     * This method sets up the necessary configurations and preconditions
     * required to start the data import process.
     *
     * @return void
     */
    protected function resetProcess()
    {
        $this->nextProcess = null;
        $this->processErrors = [];
    }

    /**
     * Resets the temporary models used during the import process.
     *
     * This method is responsible for clearing or reinitializing any temporary
     * models that are used to store data during the import process. It ensures
     * that the temporary models are in a clean state before starting a new import.
     *
     * @return void
     */
    protected function resetTempModels()
    {
        $this->tempModels = [];
    }

    /**
     * Guard against a specific process.
     *
     * This method checks and prevents the execution of the given process.
     *
     * @param  string  $process  The name of the process to guard against.
     * @return void
     *
     * @throws \Exception If the process is invalid.
     */
    protected function guardAgainstProcess(string $process)
    {
        if (! in_array($process, static::PROCESS_ORDER)) {
            throw new \Exception('Invalid process.');
        }
    }

    /**
     * Guard against the existence of a table.
     *
     * This method checks if the specified table exists and performs necessary actions
     * to handle the case where the table is already present.
     *
     * @param  string  $table  The name of the table to check.
     * @return void
     *
     * @throws \Exception If the table does not exist.
     */
    protected function guardAgaintsTableExist($table)
    {
        if (! ModelHelper::isTableExists($table)) {
            throw new \Exception("Table {$table} does not exist.");
        }
    }

    /**
     * Find field groups by name.
     *
     * @param  string[]|string  $names  The names of the field groups to find.
     * @return Collection<FieldGroup|Model>
     */
    protected function findFieldGroups(...$names)
    {
        return $this->findFromTempModels('fieldGroups', $names);
    }

    /**
     * Find templates by slug.
     *
     * @param  string[]|string  $slugs  The slugs of the templates to find.
     * @return Collection<Template|Model>
     */
    protected function findTemplates(...$slugs)
    {
        return $this->findFromTempModels('templates', $slugs);
    }

    /**
     * Find document types by slug.
     *
     * @param  string[]|string  $slugs  The slugs of the document types to find.
     * @return Collection<DocumentType|Model>
     */
    protected function findDocumentTypes(...$slugs)
    {
        return $this->findFromTempModels('documentTypes', $slugs);
    }

    /**
     * Find content by slug.
     *
     * @param  string[]|string  $slugs  The slugs of the content to find.
     * @return Collection<Content|Model>
     */
    protected function findContent(...$slugs)
    {
        $type = 'content';

        $existing = $this->tempModels[$type] ?? collect();

        $slugs = Arr::flatten($slugs);

        $missing = array_diff($slugs, $existing->keys()->toArray());

        if (! empty($missing)) {

            $model = InspireCmsConfig::getContentModelClass();

            $this->guardAgaintsTableExist($model);

            foreach ($missing as $slugPathToFind) {

                $found = $this->contentService->getBySlugPath($slugPathToFind);

                if ($found->isNotEmpty()) {

                    $existing = $existing->merge($found);

                    $this->tempModels[$type] = $existing;
                }
            }
        }

        return collect($existing)->where(fn ($v, $k) => in_array($k, $slugs));
    }

    protected function findFromTempModels(string $type, ...$keys)
    {
        $existing = $this->tempModels[$type] ?? collect();

        $keys = Arr::flatten($keys);

        $key = match ($type) {
            'fieldGroups' => 'name',
            default => 'slug',
        };

        $missing = array_diff($keys, $existing->pluck($key)->toArray());

        if (! empty($missing)) {

            $model = match ($type) {
                'fieldGroups' => InspireCmsConfig::getFieldGroupModelClass(),
                'templates' => InspireCmsConfig::getTemplateModelClass(),
                'documentTypes' => InspireCmsConfig::getDocumentTypeModelClass(),
                default => null,
            };

            if (! $model) {
                throw new \Exception("Model for type '{$type}' not found.");
            }

            $this->guardAgaintsTableExist($model);

            $found = $model::whereIn($key, $missing)->get();

            if ($found->isNotEmpty()) {

                $existing = $existing->merge($found);

                $this->tempModels[$type] = $existing;
            }
        }

        return $existing->whereIn($key, $keys);
    }

    protected function mutateFieldData(array $data): array
    {
        if (isset($data['type'])) {
            if ($data['type'] == 'contentPicker' && isset($data['config']['documentType'])) {
                $targetDocumentType = $data['config']['documentType'];
                // If it's a string and not a uuid, it's a document type slug
                if (is_string($targetDocumentType) && ! Str::isUuid($targetDocumentType)) {
                    $data['config']['documentType'] = $this->findDocumentTypes($targetDocumentType)->first()?->getKey();
                }
            }
        }

        return $data;
    }

    protected function mutateNavigationData(Entities\Navigation $item): array
    {
        $data = $item->getDataForModel();

        if (filled($item->contentSlugPath)) {
            $content = $this->findContent($item->contentSlugPath)->first();
            $data['content_id'] = $content?->getKey();
        } else {
            $data['content_id'] = null;
        }

        if (! empty($item->children) && $item->type === 'group') {
            $children = collect($item->children)->map(fn ($child) => $this->mutateNavigationData($child));
            $data['children'] = $children->toArray();
        } else {
            $data['children'] = [];
        }

        return $data;
    }
}

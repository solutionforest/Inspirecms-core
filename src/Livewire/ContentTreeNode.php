<?php

namespace SolutionForest\InspireCms\Livewire;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Lazy;
use SolutionForest\InspireCms\Filament\Forms\Components\ContentTree\Filter\BaseFilter;
use SolutionForest\InspireCms\InspireCmsConfig;

#[Lazy]
class ContentTreeNode extends \Livewire\Component
{
    public ?string $parentId = null;

    public ?string $search = null;

    public ?string $modelable = null;

    public array $filters = [];

    public array $limits = [];

    public bool $isDisabled = false;

    public function render()
    {
        return view('inspirecms::livewire.content-tree-node', [
            'records' => $this->getRecords(),
        ]);
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div>Loading...</div>
        HTML;
    }

    protected function getRecords()
    {
        /**
         * @var Model
         */
        $contentModel = app(InspireCmsConfig::getContentModelClass());
        $morph = $contentModel->getMorphClass();

        $scopedAttributes = [
            'nestable_type' => $morph,
        ];

        /**
         * @var Builder
         */
        $subQ = $contentModel->newQuery() // Without soft delete
            ->when(
                filled($this->search),
                fn ($query) => $query->where('slug', 'like', "%{$this->search}%")
            )
            ->select([
                'id as content_id',
                'slug as content_slug',
                'title as content_title',
            ]);

        if (! empty($this->filters)) {
            foreach ($this->filters as $filter) {

                [$column, $operator, $value] = $filter;

                $this->applyFilterOnQuery($subQ, $column, $operator, $value);
            }
        }

        /**
         * @var Builder
         */
        $baseQuery = InspireCmsConfig::getNestableTreeModelClass()::scoped($scopedAttributes)
            ->joinSub(
                $subQ,
                'nestable',
                'nestable_id',
                '=',
                'content_id'
            );

        $records = $baseQuery->get();

        $data = $records->each(function ($record) use ($contentModel) {
            $tempMorph = $contentModel->forceFill([
                'slug' => $record->content_slug,
                'title' => json_decode($record->content_title ?? '', true),
            ]);
            $record->key = $record->nestable_id;
            $record->title = method_exists($tempMorph, 'translate') ? $tempMorph->translate('title') : $tempMorph->title;
            $record->description = $tempMorph->slug;
        });

        return $data->toTree()->toArray();
    }

    protected function applyFilterOnQuery(&$query, $column, $operator, $value)
    {
        if ($column instanceof BaseFilter) {
            return $column->applyToQuery($query);
        }
        if ($column === 'id') {
            if ($operator == 'not') {
                $query->whereKeyNot($value);
            } else {
                $query->whereKey($value);
            }
        } elseif ($operator == 'not') {
            $query->whereNot($column, $value);
        } elseif ($operator == 'in') {
            $query->whereIn($column, $value);
        } elseif ($operator == 'not in') {
            $query->whereNotIn($column, $value);
        } else {
            $query->where($column, $operator, $value);
        }
    }
}

<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components\ContentTree\Filter;

class WithoutGlobalScope extends BaseFilter
{
    public function __construct(
        protected string $scopeClass,
    ) {}

    public function toLivewire()
    {
        return [
            'scopeClass' => $this->scopeClass,
        ];
    }

    public static function fromLivewire($value)
    {
        return new static($value['scopeClass']);
    }

    public function applyToQuery($query)
    {
        $query->withoutGlobalScope($this->scopeClass);

        return $query;
    }
}

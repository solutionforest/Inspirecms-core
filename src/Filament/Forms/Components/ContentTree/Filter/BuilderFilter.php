<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components\ContentTree\Filter;

class BuilderFilter extends BaseFilter
{
    public function __construct(
        protected string $scopeMethod,
        protected ?array $scopeParameters = null,
    ) {}

    public function toLivewire()
    {
        return [
            ... parent::toLivewire(),
            'scopeMethod' => $this->scopeMethod,
            'scopeParameters' => $this->scopeParameters,
        ];
    }

    public static function fromLivewire($value)
    {
        return new static($value['scopeMethod'], $value['scopeParameters'] ?? null);
    }

    public function applyToQuery($query)
    {
        $method = $this->scopeMethod;

        $query = call_user_func_array([$query, $method], $this->scopeParameters ?? []);

        return $query;
    }
}

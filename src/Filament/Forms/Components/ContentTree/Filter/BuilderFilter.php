<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components\ContentTree\Filter;

use Livewire\Wireable;

class BuilderFilter extends BaseFilter
{
    public function __construct(
        protected string $method,
        protected ?array $parameters = null,
    ) { }

    public function toLivewire()
    {
        return [
            'method' => $this->method,
            'parameters' => $this->parameters,
        ];
    }
 
    public static function fromLivewire($value)
    {
        return new static($value['method'], $value['parameters'] ?? null);
    }

    public function applyToQuery($query)
    {
        $method = $this->method;
        if ($this->parameters != null) {
            $query->{$method}($this->parameters);
        } else {
            $query->{$method}();
        }
        return $query;
    }
}

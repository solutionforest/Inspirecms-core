<?php

namespace SolutionForest\InspireCms\DataTypes;

use Closure;

class ContentStatusOption
{
    use \Filament\Support\Concerns\EvaluatesClosures;

    public function __construct(
        /**
         * Unique value.
         *
         * @var integer
         */
        public int $value,
        /**
         * Unique name for retrieve used.
         *
         * @var string
         */
        public string $name,
        protected null|string|Closure $label = null,
        protected null|string|Closure $color = null,
        protected null|string|Closure $icon = null,
    ) { }

    public function getLabel(): string
    {
        return $this->evaluate($this->label) ?? $this->name;
    }

    public function getColor(): ?string
    {
        return $this->evaluate($this->color);
    }

    public function getIcon(): ?string
    {
        return $this->evaluate($this->icon);
    }
}

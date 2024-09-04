<?php

namespace SolutionForest\InspireCms\DataTypes\Manifest;

use Closure;

class ContentStatusOption extends BaseManifestOption
{
    public function __construct(
        /**
         * Unique value.
         *
         * @var int
         */
        protected int $value,
        /**
         * Unique name for retrieve used.
         *
         * @var string
         */
        protected string $name,
        protected null | string | Closure $label = null,
        protected null | string | Closure $color = null,
        protected null | string | Closure $icon = null,
    ) {}

    public function getValue(): int
    {
        return $this->value;
    }

    public function getName(): string
    {
        return $this->name;
    }

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

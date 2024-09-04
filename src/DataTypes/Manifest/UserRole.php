<?php

namespace SolutionForest\InspireCms\DataTypes\Manifest;

use SolutionForest\InspireCms\Support\InspireCmsConfig;

class UserRole extends BaseManifestOption
{
    protected string $guardName;

    public function __construct(
        protected string $name,
        protected null | string | \Closure $displayName = null,
    ) {
        $this->guardName = InspireCmsConfig::getGuardName();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getGuardName(): string
    {
        return $this->guardName;
    }

    public function getDisplayName(): string
    {
        return $this->evaluate($this->displayName) ?? $this->getName();
    }
}

<?php

namespace SolutionForest\InspireCms\DataTypes\Manifest;

class UserPermission extends BaseManifestOption
{
    protected string $guardName;

    public function __construct(
        protected string $name,
        protected null | string | \Closure $displayName = null,
        protected null | string | \Closure $helperText = null,
    ) {
        $this->guardName = config('inspirecms.auth.guard', 'inspirecms');
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

    public function getHelperText(): ?string
    {
        return $this->evaluate($this->helperText);
    }
}

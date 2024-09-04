<?php

namespace SolutionForest\InspireCms\DataTypes\Manifest;

class UserRole extends BaseManifestOption
{
    protected string $guardName;

    public function __construct(
        protected string $name,
    ) {
        $this->guardName = $guardName ?? config('inspirecms.auth.guard', 'inspirecms');
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getGuardName(): string
    {
        return $this->guardName;
    }
}

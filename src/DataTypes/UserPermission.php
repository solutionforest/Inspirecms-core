<?php

namespace SolutionForest\InspireCms\DataTypes;

class UserPermission
{
    use \Filament\Support\Concerns\EvaluatesClosures;

    public string $guardName;

    public function __construct(
        public string $name,
        protected null | string | \Closure $displayName = null,
        protected null | string | \Closure $helperText = null,
    ) {
        $this->guardName = config('inspirecms.auth.guard', 'inspirecms');
    }

    public function getDisplayName(): string
    {
        return $this->evaluate($this->displayName) ?? $this->name;
    }

    public function getHelperText(): ?string
    {
        return $this->evaluate($this->helperText);
    }
}

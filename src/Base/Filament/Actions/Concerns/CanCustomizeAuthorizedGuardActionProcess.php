<?php

namespace SolutionForest\InspireCms\Base\Filament\Actions\Concerns;

use Closure;
use SolutionForest\InspireCms\Facades\PermissionManifest;

trait CanCustomizeAuthorizedGuardActionProcess
{
    protected ?Closure $authorizedCallback = null;

    public function authorizedGuardActionUsing(?Closure $callback): static
    {
        $this->authorizedCallback = $callback;

        return $this;
    }

    public function authorizeGuardProcess(?Closure $default): ?bool
    {
        return $this->evaluate($this->authorizedCallback ?? $default);
    }

    public function isVisible(): bool
    {
        $authResult = $this->authorizeGuardProcess(function () {
            return PermissionManifest::authorizeAction(static::class);
        });

        if ($authResult === false) {
            return false;
        }

        return parent::isVisible();
    }
}

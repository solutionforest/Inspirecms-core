<?php

namespace SolutionForest\InspireCms\Base\Filament\Concerns;

/**
 * Marker trait — enables Filament's cacheTraitActions() to call
 * cacheHasContentFormActions() before cacheMountedActions() runs,
 * so extra status actions are in cachedActions in time.
 */
trait HasContentFormActions {}
<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SolutionForest\InspireCms\Base\Enums\Interfaces\NavigationCategory as NavigationCategoryEnumInterface;
use SolutionForest\InspireCms\Base\Enums\Interfaces\NavigationType as NavigationTypeEnumInterface;
use SolutionForest\InspireCms\Support\Base\Models\Interfaces\HasDtoModel;

interface Navigation extends HasDtoModel
{
    public function content(): BelongsTo;

    public function getUrl(): ?string;

    public function getNavigationCategoryEnum(): ?NavigationCategoryEnumInterface;

    /**
     * Get the class name of the NavigationCategoryEnum.
     *
     * This method returns the fully qualified class name of the enumeration
     * that represents the different types of navigation.
     *
     * @return string The class name of the NavigationCategoryEnumInterface.
     */
    public static function getNavigationCategoryEnumClass(): string;

    public function getNavigationTypeEnum(): ?NavigationTypeEnumInterface;

    /**
     * Get the class name of the NavigationCategoryEnum.
     *
     * This method returns the fully qualified class name of the enumeration
     * that represents the different types of navigation.
     *
     * @return string The class name of the NavigationTypeEnumInterface.
     */
    public static function getNavigationTypeEnumClass(): string;
}

<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Base\Enums\Interfaces\NavigationCategory as NavigationCategoryEnumInterface;
use SolutionForest\InspireCms\Base\Enums\Interfaces\NavigationType as NavigationTypeEnumInterface;
use SolutionForest\InspireCms\Base\Models\Interfaces\ActivableEntity;
use SolutionForest\InspireCms\Base\Models\Interfaces\HasLocaleUrl;

/**
 * @property int $id
 * @property string $content_id
 * @property array<string,?string> $url
 * @property string $target
 * @property array<string,?string> $title
 * @property string $type
 * @property string $category
 * @property bool $is_active
 * @property int $_lft
 * @property int $_rgt
 * @property ?int $parent_id
 * @property ?\Carbon\CarbonInterface $created_at
 * @property ?\Carbon\CarbonInterface $updated_at
 * @property-read null | Model & Content $content
 */
interface Navigation extends ActivableEntity, HasLocaleUrl
{
    /**
     * Get the content associated with the navigation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function content();

    /**
     * Get the navigation category enumeration.
     *
     * @return NavigationCategoryEnumInterface|null The navigation category enumeration, or null if not set.
     */
    public function getNavigationCategoryEnum();

    /**
     * Get the class name of the NavigationCategoryEnum.
     *
     * This method returns the fully qualified class name of the enumeration
     * that represents the different types of navigation.
     *
     * @return string The class name of the NavigationCategoryEnumInterface.
     */
    public static function getNavigationCategoryEnumClass();

    /**
     * Get the navigation type enum.
     *
     * @return NavigationTypeEnumInterface|null The navigation type enum or null if not set.
     */
    public function getNavigationTypeEnum();

    /**
     * Get the class name of the NavigationCategoryEnum.
     *
     * This method returns the fully qualified class name of the enumeration
     * that represents the different types of navigation.
     *
     * @return string The class name of the NavigationTypeEnumInterface.
     */
    public static function getNavigationTypeEnumClass();

    /**
     * Get the default content ID.
     *
     * @return string|int|null The default content ID, which can be a string, an integer, or null.
     */
    public static function defaultContentId();

    /**
     * Determine if the navigation item is visible.
     *
     * @return bool True if the navigation item is visible, false otherwise.
     */
    public function isVisibility();

    /**
     * Scope a query to only include navigation items of a given category type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCategory($query, string $type);

    /**
     * Scope a query to only include active records.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereIsActive($query, bool $condition = true);
}

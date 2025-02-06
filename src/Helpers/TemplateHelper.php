<?php

namespace SolutionForest\InspireCms\Helpers;

class TemplateHelper
{
    /**
     * Splits a Blade expression into its individual components.
     *
     * @param string $bladeExpression The Blade expression to be split.
     * @return array An array containing the split components of the Blade expression.
     */
    public static function splitBladeExpression(string $bladeExpression)
    {
        $explodedValues = array_map('trim', explode(',', $bladeExpression));

        if (count($explodedValues) > 3) {
            [$group, $property, $propertyVarName, $dtoVar] = $explodedValues;
        } elseif (count($explodedValues) > 2) {
            [$group, $property, $propertyVarName] = $explodedValues;
        } else {
            [$group, $property] = $explodedValues;
        }

        $group = trim($group, "'\"");
        $property = trim($property, "'\"");

        $propertyVarName ??= "{$group}_{$property}";
        $dtoVar ??= '$content';

        $propertyVarName = ltrim(trim($propertyVarName, "'\""), '$');

        return [$group, $property, $dtoVar, $propertyVarName];
    }
}

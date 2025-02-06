<?php

namespace SolutionForest\InspireCms\Helpers;

class TemplateHelper
{
    /**
     * Splits a Blade expression to extract a property.
     *
     * This method takes a Blade expression as input and splits it to retrieve
     * the property specified within the expression.
     *
     * @param string $bladeExpression The Blade expression to be split.
     * @return array An array containing the split parts of the Blade expression.
     */
    public static function splitBladeExpressionForProperty(string $bladeExpression)
    {
        $explodedValues = array_map('trim', explode(',', $bladeExpression));

        if (count($explodedValues) > 3) {
            [$group, $property, $propertyVarName, $dtoVar] = $explodedValues;
        } elseif (count($explodedValues) > 2) {
            [$group, $property, $propertyVarName] = $explodedValues;
        } else {
            [$group, $property] = $explodedValues;
        }

        $group = static::normalizeVarNameFromBladeExpression($group);
        $property = static::normalizeVarNameFromBladeExpression($property);

        $propertyVarName ??= static::generatePropertyVarName($group, $property);
        $dtoVar ??= '$content';

        return [$group, $property, $dtoVar, static::normalizeVarNameFromBladeExpression($propertyVarName)];
    }

    /**
     * Generates a variable name for a given property within a specified group.
     *
     * @param string $group The group to which the property belongs.
     * @param string $property The property for which the variable name is generated.
     * @return string The generated variable name.
     */
    public static function generatePropertyVarName($group, $property)
    {
        $group = static::normalizeVarNameFromBladeExpression($group);
        $property = static::normalizeVarNameFromBladeExpression($property);

        return "{$group}_{$property}";
    }

    protected static function normalizeVarNameFromBladeExpression($text)
    {
        return ltrim(trim($text, "'\""), '$');
    }
}

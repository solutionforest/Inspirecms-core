<?php

namespace SolutionForest\InspireCms\Fields\Converters;

use Closure;
use Illuminate\Support\Traits\Macroable;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\InspireCms\Support\Helpers\TranslatableHelper;

abstract class BaseConverter
{
    use Macroable;

    protected FieldTypeConfig $fieldTypeConfig;

    /**
     * @var array<class-string<BaseConverter>, array<Closure>>
     */
    protected static $configurations = [];

    public function __construct(FieldTypeConfig $fieldTypeConfig)
    {
        $this->fieldTypeConfig = $fieldTypeConfig;

        $this->configure();
    }

    abstract public function toDisplayValue(mixed $sourceValue, ?string $locale, ?string $fallbackLocale);

    protected function applyLocaleConversion(mixed $sourceValue, ?string $locale, ?string $fallbackLocale)
    {
        if ($this->isFieldTypeTranslatable()) {
            return TranslatableHelper::getTranslations(
                $sourceValue,
                $locale ?? $fallbackLocale,
                $fallbackLocale
            );
        }

        return $sourceValue;
    }

    protected function isFieldTypeTranslatable(): bool
    {
        return $this->fieldTypeConfig->isTranslatable();
    }

    public static function configureUsing(Closure $modifyUsing)
    {
        static::$configurations ??= [];
        static::$configurations[static::class][] = $modifyUsing;
    }

    public function configure(): static
    {
        $class = static::class;
        $configurations = static::$configurations[$class] ?? [];

        if (count($configurations) > 0) {
            foreach ($configurations as $configuration) {
                if (! is_callable($configuration)) {
                    continue;
                }

                $configuration($this);
            }
        }

        return $this;
    }
}

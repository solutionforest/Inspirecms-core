<?php

namespace SolutionForest\InspireCms\Fields\Converters;

use Closure;
use Illuminate\Support\Traits\Macroable;
use SolutionForest\FilamentFieldGroup\FieldTypes\Configs\Contracts\FieldTypeConfig;
use SolutionForest\InspireCms\Support\Helpers\TranslatableHelper;

abstract class BaseConverter
{
    use Macroable;

    /**
     * @var FieldTypeConfig
     */
    protected $fieldTypeConfig;

    /**
     * @var string|null
     */
    protected $fieldKey;

    /**
     * @var string|null
     */
    protected $fieldGroupKey;

    /**
     * @var array<class-string<BaseConverter>, array<Closure>>
     */
    protected static $configurations = [];

    /**
     * @param  ?string  $group
     * @param  ?string  $key
     */
    public function __construct(FieldTypeConfig $fieldTypeConfig, $group, $key)
    {
        $this->fieldTypeConfig = $fieldTypeConfig;

        $this->fieldKey = $key;
        $this->fieldGroupKey = $group;

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
        return $this->getFieldTypeConfig()->isTranslatable();
    }

    public function getFieldTypeConfig(): FieldTypeConfig
    {
        return $this->fieldTypeConfig;
    }

    /**
     * @return string|null
     */
    public function getKey()
    {
        return $this->fieldKey;
    }

    /**
     * @return string|null
     */
    public function getGroup()
    {
        return $this->fieldGroupKey;
    }

    public function getFieldIdentifier(): string
    {
        return collect([$this->getGroup(), $this->getKey()])
            ->filter()
            ->implode('.');
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

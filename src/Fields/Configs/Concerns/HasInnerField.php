<?php

namespace SolutionForest\InspireCms\Fields\Configs\Concerns;

trait HasInnerField
{
    protected array $fieldVariable = [];

    public function setFieldVariable(array $variable): static
    {
        $this->fieldVariable = $variable;

        return $this;
    }
}

<?php

namespace SolutionForest\InspireCms\DataTypes\Manifest;

class ClusterSection extends BaseManifestOption
{
    public function __construct(
        /**
         * Unique name.
         *
         * @var string
         */
        protected string $name,
        protected string $fqcn,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getFqcn(): string
    {
        return $this->fqcn;
    }
}

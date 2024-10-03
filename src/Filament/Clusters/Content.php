<?php

namespace SolutionForest\InspireCms\Filament\Clusters;

use Filament\Clusters\Cluster;
use SolutionForest\InspireCms\Filament\Concerns\ClusterSectionTrait;
use SolutionForest\InspireCms\Filament\Contracts\ClusterSection;

class Content extends Cluster implements ClusterSection
{
    use ClusterSectionTrait;

    protected static ?string $navigationIcon = 'heroicon-o-folder';
}

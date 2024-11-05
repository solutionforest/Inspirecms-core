<?php

namespace SolutionForest\InspireCms\Filament\Widgets;

use Filament\Widgets\Widget;

class AlertOverview extends Widget
{
    protected static string $view = 'inspirecms::filament.widgets.alert-overview.index';

    protected int | string | array $columnSpan = 'full';

    protected static bool $isLazy = false;

    /**
     * @var array<Alert> | null
     */
    protected ?array $cachedAlerts = null;

    /**
     * @return array<Alert>
     */
    protected function getAlerts(): array
    {
        return [];
    }

    protected function getCachedAlerts(): array
    {
        return $this->cachedAlerts ??= $this->getAlerts();
    }

    /**
     * @return int|array<string,int>
     */
    public function getColumns(): int|array
    {
        return 1;
    }
}

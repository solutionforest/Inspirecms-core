<?php

namespace SolutionForest\InspireCms\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;
use SolutionForest\InspireCms\View\Components\Alert;

class AlertOverview extends Widget
{
    protected string $view = 'inspirecms::filament.widgets.alert-overview';

    protected int | string | array $columnSpan = 'full';

    protected static bool $isLazy = true;

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
    public function getColumns(): int | array
    {
        return 1;
    }

    public function placeholder(): View
    {
        return view(
            'inspirecms::components.loading-bar',
            [
                'height' => $this->getPlaceholderHeight(),
                ...$this->getPlaceholderData(),
            ],
        );
    }

    public function getPlaceholderHeight(): string
    {
        if (count($this->getCachedAlerts()) <= 0) {
            return '0px';
        }

        return '12px';
    }
}

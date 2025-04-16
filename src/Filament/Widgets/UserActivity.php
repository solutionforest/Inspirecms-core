<?php

namespace SolutionForest\InspireCms\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\Widget;
use Livewire\WithPagination;
use SolutionForest\InspireCms\Filament\Contracts\GuardWidget;
use SolutionForest\InspireCms\Filament\Widgets\Conceners\GuardWidgetTrait;
use SolutionForest\InspireCms\Models\Contracts\UserLoginActivity;

class UserActivity extends Widget implements GuardWidget
{
    use GuardWidgetTrait;
    use WithPagination;

    protected static string $view = 'inspirecms::filament.widgets.user-activity';

    protected int | string | array $columnSpan = 'full';

    protected static ?string $pollingInterval = '5s';

    public static function getPermissionName(): string
    {
        return 'widgets_view-user-activity';
    }

    public static function getPermissionDisplayName(): string
    {
        return strval(__('inspirecms::widgets.user_activity.permission_display_name'));
    }

    public function getViewData(): array
    {
        return [
            'activities' => $this->getUserActivities(),
        ];
    }

    protected function getUserActivities()
    {
        $pageName = 'user-activity';

        $user = auth()->user();

        try {

            if ($user && is_inspirecms_user($user)) {

                $activities = $user->userActivities()
                    ->latest('last_logged_in_at_utc')
                    ->simplePaginate(perPage: 5, pageName: $pageName, page: $this->getPage($pageName));

                $activities->tap(function ($activities) use ($user) {
                    $activities->setCollection($activities->getCollection()->map(function (UserLoginActivity $activity) use ($user) {

                        $activity->causer = $user;
                        $activity->subject = $user;

                        $activity->description = $activity->ip_address;

                        $activity->last_logged_in_at_utc = $this->convertDt($activity->last_logged_in_at_utc);
                        $activity->last_logged_in_at_local = $this->convertDtToLocal($activity->last_logged_in_at_utc);

                        $activity->last_logged_out_at_utc = $this->convertDt($activity->last_logged_out_at_utc);
                        $activity->last_logged_out_at_local = $this->convertDtToLocal($activity->last_logged_out_at_utc);

                        return $activity;

                    }));
                });

                return $activities;

            }

        } catch (\Throwable $th) {
            //
        }

        // empty pagination

        return \Illuminate\Pagination\Paginator::currentPageResolver(function ($pageName) {
            return $this->getPage($pageName);
        });
    }

    private function convertDt(?\Carbon\CarbonInterface $dateTime): ?\Carbon\CarbonInterface
    {
        if (is_null($dateTime)) {
            return null;
        }

        $dtFormat = 'Y-m-d H:i:s';

        $ts = $dateTime->format($dtFormat);

        return Carbon::createFromFormat(
            $dtFormat,
            $ts,
            'UTC'
        );
    }

    private function convertDtToLocal(?\Carbon\CarbonInterface $dateTime): ?\Carbon\CarbonInterface
    {
        return $this->convertDt($dateTime)?->setTimezone(config('app.timezone'));
    }
}

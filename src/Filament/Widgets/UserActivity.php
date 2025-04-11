<?php

namespace SolutionForest\InspireCms\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\WithPagination;
use SolutionForest\InspireCms\Filament\Contracts\GuardWidget;
use SolutionForest\InspireCms\Filament\Widgets\Conceners\GuardWidgetTrait;

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
                    $activities->setCollection($activities->getCollection()->map(function ($activity) use ($user) {
                        
                        $activity->causer = $user;
                        $activity->subject = $user;

                        $activity->description = $activity->ip_address;

                        $dtFormat = 'Y-m-d H:i:s';
                        if ($activity->last_logged_in_at_utc != null) {
                            $ts = $activity->last_logged_in_at_utc->format($dtFormat);
                            $activity->last_logged_in_at_utc = Carbon::createFromFormat(
                                $dtFormat, 
                                $ts,
                                'UTC'
                            );
                            $activity->last_logged_in_at_local = Carbon::createFromFormat(
                                $dtFormat, 
                                $ts,
                                'UTC'
                            )->setTimezone(config('app.timezone'));
                        } else {
                            $activity->last_logged_in_at_local = null;
                        }
                        
                        if ($activity->last_logged_out_at_utc != null) {
                            $ts = $activity->last_logged_out_at_utc->format($dtFormat);
                            $activity->last_logged_out_at_utc = Carbon::createFromFormat(
                                $dtFormat, 
                                $ts,
                                'UTC'
                            );
                            $activity->last_logged_out_at_local = Carbon::createFromFormat(
                                $dtFormat, 
                                $ts,
                                'UTC'
                            )->setTimezone(config('app.timezone'));
                        } else {
                            $activity->last_logged_out_at_local = null;
                        }
                        $activity->last_logged_out_at_utc = \Carbon\Carbon::createFromFormat(
                            $dtFormat, 
                            $activity->last_logged_out_at_utc->format($dtFormat), 
                            'UTC'
                        );

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
}

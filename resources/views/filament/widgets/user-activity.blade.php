<x-filament::widget class="filament-widgets-user-activity-widget">
    <x-filament::section>
        <x-slot name="heading">
            {{ __('inspirecms::widgets.user_activity.title') }}
        </x-slot>
        
        @if($activities && $activities->count())
            <div class="space-y-4">
                @foreach($activities as $activity)
                    <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                @if($activity->causer && $activity->causer->avatar)
                                    <img src="{{ $activity->causer->avatar }}" alt="{{ $activity->causer->name }}" class="w-10 h-10 rounded-full">
                                @else
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-500">
                                        <span class="text-white text-sm font-medium">
                                            {{ $activity->causer ? substr($activity->causer->name ?? '', 0, 2) : 'SYS' }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <p class="font-medium text-sm">
                                    {{ $activity->causer->name ?? 'System' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $activity->description }}
                                    @if($activity->subject)
                                        <span class="font-medium">{{ get_class($activity->subject) == 'App\Models\User' ? $activity->subject->name : class_basename($activity->subject) }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 flex flex-col items-end">
                            @if($activity->last_logged_in_at_local)
                                <span>Login: {{ $activity->last_logged_in_at_local->diffForHumans() }}</span>
                            @endif
                            @if($activity->last_logged_out_at_local)
                                <span>Logout: {{ $activity->last_logged_out_at_local->diffForHumans() }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            
            @if($activities->hasPages())
                <div class="mt-2">
                    {{ $activities->links() }}
                </div>
            @endif
        @else
            <div class="flex items-center justify-center h-32 text-gray-500 dark:text-gray-400">
                {{ __('inspirecms::widgets.user_activity.empty_state.heading') }}
            </div>
        @endif
    </x-filament::section>
</x-filament::widget>
<?php

namespace SolutionForest\InspireCms\Filament\Resources\Users\Schemas\Components;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Support\Enums\TextSize;
use SolutionForest\InspireCms\Filament\Resources\Users\Actions\ResendUserVerificationEmailAction;
use SolutionForest\InspireCms\Filament\Resources\Users\Actions\ResetUserLockoutAction;
use SolutionForest\InspireCms\Filament\Resources\Users\Actions\SetUserAccountVerifiedAction;
use SolutionForest\InspireCms\Helpers\AuthHelper;

class UserDetailDisplayGroup
{
    public static function make(): Section
    {
        return Section::make()
            ->columns(1)
            ->visibleOn(['edit', 'view'])
            ->inlineLabel()
            ->schema([

                TextEntry::make('id')
                    ->label(__('inspirecms::inspirecms.id'))
                    ->copyable(),

                TextEntry::make('uuid')
                    ->label(__('inspirecms::inspirecms.uuid'))
                    ->copyable(),

                TextEntry::make('last_logged_in_at')
                    ->label(__('inspirecms::resources/user.last_logged_in_at.label'))
                    ->dateTime(),

                TextEntry::make('failed_login_attempt')
                    ->label(__('inspirecms::resources/user.failed_login_attempt.label'))
                    ->suffix('/' . AuthHelper::maxAttempts())
                    ->belowContent([
                        ResetUserLockoutAction::make(),
                    ]),

                TextEntry::make('last_lockouted_at')
                    ->label(__('inspirecms::resources/user.last_lockouted_at.label'))
                    ->dateTime()
                    ->belowContent(function ($record) {
                        if (empty($record->locked_until)) {
                            return [];
                        }

                        return [
                            Text::make(__('inspirecms::resources/user.last_lockouted_at.hints', ['time' => $record->locked_until]))
                                ->tooltip($record->locked_until?->diffForHumans())
                                ->size(TextSize::ExtraSmall),
                        ];
                    }),

                Actions::make([
                    SetUserAccountVerifiedAction::make(),
                    ResendUserVerificationEmailAction::make(),
                ])->alignEnd(),

                TextEntry::make('email_confirmed_at')
                    ->label(__('inspirecms::resources/user.email_confirmed_at.label'))
                    ->dateTime(),

                TextEntry::make('created_at')
                    ->label(__('inspirecms::inspirecms.created_at'))
                    ->dateTime(),

                TextEntry::make('updated_at')
                    ->label(__('inspirecms::inspirecms.last_updated_at'))
                    ->dateTime(),
            ]);
    }
}

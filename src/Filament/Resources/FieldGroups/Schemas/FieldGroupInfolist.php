<?php

namespace SolutionForest\InspireCms\Filament\Resources\FieldGroups\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Icon;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use SolutionForest\InspireCms\Models\Contracts\Field;

class FieldGroupInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                RepeatableEntry::make('fields')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->columns(6)
                    ->schema([
                        TextEntry::make('name')
                            ->columnSpan(1)
                            ->hiddenLabel()
                            ->state(fn ($record) => $record->field_type_config[0]['name'] ?? null)
                            ->afterContent(function ($record) {
                                $icon = $record->field_type_config[0]['icon'] ?? 'heroicon-o-minus-circle';
                                if (filled($icon) && str_starts_with($icon, 'inspirecms::')) {
                                    return FilamentIcon::resolve($icon);
                                } elseif (filled($icon)) {
                                    return Icon::make($icon)
                                        ->color('gray');
                                }

                                return null;
                            }),

                        Group::make([

                            TextEntry::make('label')
                                ->label(__('inspirecms::resources/field.label.label'))
                                ->inlineLabel(),

                            TextEntry::make('name')
                                ->label(__('inspirecms::resources/field.name.label'))
                                ->inlineLabel()
                                ->badge(),

                            IconEntry::make('translatable')
                                ->label(__('inspirecms::resources/field.translatable.label'))
                                ->inlineLabel()
                                ->state(fn (Model | Field $record) => Arr::get($record->config ?? [], 'translatable', false) === true)
                                ->boolean()
                                ->falseColor('gray'),

                        ])->columnSpan(5),
                    ]),
            ]);
    }
}

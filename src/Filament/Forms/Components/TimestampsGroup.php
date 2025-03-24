<?php

namespace SolutionForest\InspireCms\Filament\Forms\Components;

use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use SolutionForest\InspireCms\Helpers\UIHelper;

class TimestampsGroup extends Group
{
    protected function setUp(): void
    {
        parent::setUp();

        if (blank($this->childComponents)) {

            $this
                ->visibleOn(['edit', 'view'])
                ->schema([

                    Placeholder::make('created_at')
                        ->content(fn (?Model $record) => $record?->created_at)
                        ->label(__('inspirecms::inspirecms.created_at'))
                        ->inlineLabel(),
                    Placeholder::make('updated_at')
                        ->content(fn (?Model $record) => $record?->updated_at)
                        ->label(__('inspirecms::inspirecms.last_updated_at'))
                        ->inlineLabel(),

                    Placeholder::make('deleted_at')
                        ->content(function (?Model $record) {
                            if (! $record) {
                                return null;
                            }
                            $isInTrashed = $record->trashed();
                            if (! $isInTrashed) {
                                return null;
                            }
                            $dt = $record?->deleted_at;
                            $iconHtml = UIHelper::generateBooleanIcon(
                                condition: true,
                                trueIcon: FilamentIcon::resolve('inspirecms::recycle_bin'),
                                trueColor: 'danger',
                            );

                            return new HtmlString(Blade::render(<<<'blade'
                                <span 
                                    class="text-custom-500 dark:text-custom-400"
                                    style="{{$textStyle}}"
                                >
                                    {{ $dt }}
                                </span>
                                <span>{{ $iconHtml }}</span>
                            blade, [
                                'dt' => $dt,
                                'iconHtml' => $iconHtml,
                                'textStyle' => \Filament\Support\get_color_css_variables(
                                    'danger',
                                    shades: [400, 500],
                                ),
                            ]));
                        })
                        ->visible(function (?Model $record) {

                            if (! $record) {
                                return null;
                            }

                            $traits = class_uses_recursive($record);

                            if (! in_array(SoftDeletes::class, $traits)) {
                                return null;
                            }

                            return $record->trashed();
                        })
                        ->label(__('inspirecms::inspirecms.deleted_at'))
                        ->extraAttributes(['class' => 'font-semibold inline-flex items-center gap-x-2'])
                        ->inlineLabel(),
                ]);
        }
    }
}

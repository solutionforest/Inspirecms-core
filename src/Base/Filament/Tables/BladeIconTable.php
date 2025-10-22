<?php

namespace SolutionForest\InspireCms\Base\Filament\Tables;

use BladeUI\Icons\Factory;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use SolutionForest\InspireCms\Filament\Tables\Columns\BladeIconColumn;

class BladeIconTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->records(function (int $page, int $recordsPerPage, $search, $filters) use ($table) {

                $arguments = $table->getArguments();
                $selected = $arguments['selected'] ?? null;

                $icons = collect(static::getAvailableIcons())->keyBy('key');

                // Push the selected icon to the front if it exists
                if (filled($selected)) {
                    $selected = is_array($selected) ? $selected : [$selected];
                    $selectedIcons = collect($selected)->mapWithKeys(function ($icon) use ($icons) {
                        return [$icon => $icons->pull($icon)];
                    });
                    $icons = $selectedIcons->merge($icons);
                }

                if ($search && filled($search)) {
                    $icons = $icons->filter(function ($icon) use ($search) {
                        return str_contains($icon['key'], $search);
                    });
                }

                if ($filters && is_array($filters)) {
                    if (
                        ($setFilter = $filters['set']['value'] ?? null) && filled($setFilter)
                    ) {
                        // dd($setFilter, $icons);
                        $icons = $icons->filter(function ($icon) use ($setFilter) {
                            return $icon['set'] === $setFilter;
                        });
                    }
                }

                $records = $icons->forPage($page, $recordsPerPage);

                return new LengthAwarePaginator(
                    $records,
                    total: count($icons), // Total number of records across all pages
                    perPage: $recordsPerPage,
                    currentPage: $page,
                );
            })
            ->contentGrid([
                'md' => 3,
            ])
            ->columns([
                Stack::make([
                    BladeIconColumn::make('icon')
                        ->getStateUsing(fn ($record) => $record['key'] ?? null),
                    TextColumn::make('key')
                        ->label('Name')
                        ->searchable()
                        ->sortable()
                        ->size(TextSize::ExtraSmall)
                        ->alignCenter()
                        ->verticallyAlignEnd(),
                    TextColumn::make('set')
                        ->size(TextSize::ExtraSmall)
                        ->color('gray')
                        ->badge()
                        ->alignCenter()
                        ->verticallyAlignEnd(),
                ]),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('set')
                    ->options(fn () => static::getAvailableSets()),
            ]);
    }

    private static function getAvailableSets()
    {
        return collect(app(Factory::class)->all())
            ->mapWithKeys(fn ($data, $set) => [$set => $set])
            ->all();
    }

    private static function getAvailableIcons()
    {
        return collect(app(Factory::class)->all())
            ->flatMap(function ($data, $set) {
                $paths = $data['paths'] ?? [];
                if (! is_array($paths)) {
                    return [];
                }
                if (empty($data['prefix']) ?? '') {
                    return [];
                }

                $availableIcons = [];
                foreach ($paths as $path) {
                    // Search svg
                    $files = scandir($path);
                    foreach ($files as $file) {
                        if (
                            ! pathinfo($file, PATHINFO_EXTENSION) === 'svg' ||
                            in_array($file, ['.', '..'])
                        ) {
                            continue;
                        }
                        $iconName = pathinfo($file, PATHINFO_FILENAME);
                        $fullIconName = $data['prefix'] . '-' . $iconName;

                        $availableIcons[] = [
                            'set' => $set,
                            'key' => $fullIconName,
                            'prefix' => $data['prefix'],
                            'name' => $iconName,
                        ];
                    }
                }

                return $availableIcons;
            })
            ->all();
    }
}

<?php

namespace SolutionForest\InspireCms\Base\Filament\Actions\Concerns;

use Filament\Forms;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Events\Content\UpsertRoute;
use SolutionForest\InspireCms\Factories\ContentSegmentFactory;
use SolutionForest\InspireCms\InspireCmsConfig;
use SolutionForest\InspireCms\Models\Contracts\Content;
use SolutionForest\InspireCms\Models\Contracts\ContentRoute;

trait UpdateContentRouteActionTrait
{
    public static function getDefaultName(): ?string
    {
        return 'update_content_route';
    }

    protected function setUpAction(): void
    {
        // todo: add translation

        $this->color('gray');

        $this->groupedIcon('heroicon-o-globe-alt');

        $this->label('Update Route');

        $this->successNotificationTitle('Route updated!');

        $this->authorize('update');

        $this->model(InspireCmsConfig::getContentModelClass());

        $this->hidden(static function ($record): bool {

            if (! $record) {
                return true;
            }

            if (! $record instanceof Content) {
                return true;
            }

            if (! $record->isWebPage()) {
                return true;
            }

            if ($record->isLocked()) {
                return true;
            }

            return false;
        });

        $factory = ContentSegmentFactory::create();

        $this
            ->slideOver()
            ->fillForm(fn (Content $record) => [
                'exists' => $record->routes?->map(fn (Model | ContentRoute $c) => $c->getKey())->toArray() ?? [],
                'data' => $record->routes
                    ?->map(fn (Model | ContentRoute $c) => $c->makeHidden(['content_id']))
                    ->toArray() ?? [],
            ])
            ->form([

                Forms\Components\Hidden::make('exists'),

                Forms\Components\Repeater::make('data')
                    ->hiddenLabel()
                    ->validationAttribute('data')
                    ->addActionLabel('Add')
                    ->addAction(fn ($action) => $action->extraAttributes(['class' => 'w-full']))
                    ->reorderable(false)
                    ->columns(2)
                    ->schema([

                        Forms\Components\Hidden::make('id'),

                        Forms\Components\Toggle::make('is_default_pattern')
                            ->columnSpanFull()
                            ->live()
                            ->label(fn ($state) => $state ? 'Default pattern' : 'Custom pattern')
                            ->validationAttribute('is default pattern')
                            ->hint(function ($state) use ($factory) {
                                if ($state) {
                                    return 'Pattern: ' . $factory->getDefaultRoutePattern();
                                }

                                return null;
                            })
                            ->onColor('success')
                            ->offColor('gray')
                            ->default(true),

                        Forms\Components\Select::make('language_id')
                            ->label('Locale')->validationAttribute('locale')
                            ->options(collect(inspirecms()->getAllAvailableLanguages())->mapWithKeys(fn ($langDto) => [$langDto->id => $langDto->code]))
                            ->placeholder('Default locale')
                            ->markAsRequired(),

                        Forms\Components\TextInput::make('uri')
                            ->label('Path')->validationAttribute('path')
                            ->required(),

                        Forms\Components\KeyValue::make('regex_constraints')
                            ->label('Regex Constraints')
                            ->columnSpanFull()
                            ->addActionLabel('Add Constraint')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->keyPlaceholder('{slug}')
                            ->valuePlaceholder('^[a-z0-9-]+$')
                            ->reorderable()
                            ->hint(implode(' ', [
                                'Add regex constraints to the route pattern.',
                                'Example: ' . collect($factory->getDefaultRouteConstraints())->map(fn ($value, $key) => '{' . $key . '} => ' . $value)->take(2)->values()->join(', '),
                                '. . . ',
                            ])),

                    ]),
            ])
            ->action(function (null | Model | Content $record, array $data, $action) {

                if (! $record || ! $record->isWebPage() || empty($data['data'] ?? [])) {
                    return;
                }

                event(new UpsertRoute(
                    content: $record->withoutRelations(),
                    data: $data['data'],
                    toRemove: collect($data['exists'] ?? [])
                        ->diff(collect($data['data'] ?? [])->map(fn ($d) => $d['id'] ?? null)->filter())
                        ->filter()
                        ->values()
                        ->toArray()
                ));

                $action->success();
            });
    }
}

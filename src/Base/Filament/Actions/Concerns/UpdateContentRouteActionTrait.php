<?php

namespace SolutionForest\InspireCms\Base\Filament\Actions\Concerns;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
        return 'updateContentRoute';
    }

    protected function setUpAction(): void
    {
        $this->color('gray');

        $this->groupedIcon('heroicon-o-globe-alt');

        $this->label(fn () => __('inspirecms::buttons.update_content_route.label'));

        $this->modalHeading(fn () => __('inspirecms::buttons.update_content_route.heading'));

        $this->successNotificationTitle(__('inspirecms::buttons.update_content_route.messages.success.title'));

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
        $routePathDocLink = 'https://laravel.com/docs/11.x/routing#route-parameters';
        $regexConstraintsDocLink = 'https://laravel.com/docs/11.x/routing#parameters-regular-expression-constraints';

        $this
            ->slideOver()
            ->fillForm(fn (Content $record) => [
                'exists' => $record->routes?->map(fn (Model | ContentRoute $c) => $c->getKey())->toArray() ?? [],
                'data' => $record->routes
                    ?->map(fn (Model | ContentRoute $c) => $c->makeHidden(['content_id']))
                    ->toArray() ?? [],
            ])
            ->form([

                Hidden::make('exists'),

                Repeater::make('data')
                    ->hiddenLabel()
                    ->validationAttribute('data')
                    ->addActionLabel(__('inspirecms::buttons.add.label'))
                    ->addAction(fn ($action) => $action->extraAttributes(['class' => 'w-full']))
                    ->reorderable(false)
                    ->columns(2)
                    ->schema([

                        Hidden::make('id'),

                        Checkbox::make('is_default_pattern')
                            ->live()
                            ->label(__('inspirecms::resources/content.routes.is_default_pattern.label'))
                            ->validationAttribute(__('inspirecms::resources/content.routes.is_default_pattern.validation_attribute'))
                            ->hint(fn () => __('inspirecms::resources/content.routes.is_default_pattern.hints', ['format' => $factory->getDefaultRoutePattern()]))
                            ->default(true),

                        Grid::make(2)
                            ->columnSpanFull()
                            ->schema([

                                Select::make('language_id')
                                    ->label(__('inspirecms::resources/content.routes.language_id.label'))
                                    ->validationAttribute(__('inspirecms::resources/content.routes.language_id.validation_attribute'))
                                    ->placeholder(__('inspirecms::resources/content.routes.language_id.placeholder'))
                                    ->options(collect(inspirecms()->getAllAvailableLanguages())->mapWithKeys(fn ($langDto) => [$langDto->id => $langDto->code]))
                                    ->markAsRequired(),

                                TextInput::make('uri')
                                    ->label(__('inspirecms::resources/content.routes.uri.label'))
                                    ->validationAttribute(__('inspirecms::resources/content.routes.uri.validation_attribute'))
                                    ->hint(__('inspirecms::resources/content.routes.uri.hints'))
                                    ->helperText(fn () => str(__('inspirecms::messages.please_refer_to_doc_link', ['link' => $routePathDocLink]))->toHtmlString())
                                    ->required(),
                            ]),

                        KeyValue::make('regex_constraints')
                            ->columnSpanFull()
                            ->label(__('inspirecms::resources/content.routes.regex_constraints.label'))
                            ->validationAttribute(__('inspirecms::resources/content.routes.regex_constraints.validation_attribute'))
                            ->addActionLabel(fn () => __('inspirecms::buttons.add_with_name.label', ['name' => (string) str(__('inspirecms::resources/content.routes.regex_constraints.label'))->lower()]))
                            ->keyLabel(__('inspirecms::resources/content.routes.regex_constraints.key_label'))
                            ->valueLabel(__('inspirecms::resources/content.routes.regex_constraints.value_label'))
                            ->keyPlaceholder('slug')
                            ->valuePlaceholder('^[a-z0-9-]+$')
                            ->hint(
                                fn () => str(__('inspirecms::resources/content.routes.regex_constraints.hints', ['examples' => collect($factory->getDefaultRouteConstraints())->take(2)->merge(['id' => '[0-9]+'])->map(fn ($v, $k) => "{{$k}} = {$v}")->values()->join(' ; ', ' ; ')]))
                                    ->finish('<br/>')
                                    ->finish(__('inspirecms::messages.please_refer_to_doc_link', ['link' => $regexConstraintsDocLink]))
                                    ->toHtmlString()
                            ),

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

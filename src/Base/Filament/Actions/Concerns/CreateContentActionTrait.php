<?php

namespace SolutionForest\InspireCms\Base\Filament\Actions\Concerns;

use Closure;
use Filament\Forms\Components\TableSelect;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Model;
use SolutionForest\InspireCms\Filament\Resources\Contents\Tables\DocumentTypesAssociationTable;
use SolutionForest\InspireCms\InspireCmsConfig;

trait CreateContentActionTrait
{
    protected null | Closure | string | int $parentContentKey = null;

    protected null | Closure | string | int | Model $parentDocumentType = null;

    protected ?Closure $documentTypeTitleUsing = null;

    protected ?Closure $nodeTitleUsing = null;

    public static function getDefaultName(): ?string
    {
        return 'createContent';
    }

    protected function setUpAction(): void
    {
        $this->model(InspireCmsConfig::getContentModelClass());

        $this->authorize('create');

        $this->label(__('inspirecms::buttons.create_content.label'));

        $this->icon(FilamentIcon::resolve('inspirecms::add'));

        $this->slideOver();

        $this->modal();

        $this->modalWidth('5xl');

        $this->stickyModalHeader();

        $this->modalHeading(function () {
            $title = $this->evaluate($this->nodeTitleUsing);

            if (! is_string($title)) {
                $title = null;
            }

            if (blank($title)) {
                return __('inspirecms::buttons.create_content.label');
            }

            return __('inspirecms::buttons.create_content.heading', ['title' => $title]);
        });

        $this
            ->fillForm(fn () => [])
            ->schema([
                TableSelect::make('selection')
                    ->hiddenLabel()
                    ->tableConfiguration(DocumentTypesAssociationTable::class)
                    ->tableArguments(function ($livewire) {
                        $parentDocumentType = $this->getParentDocumentType();
                        $parentDocumentTypeId = $parentDocumentType instanceof Model ? $parentDocumentType->getKey() : $parentDocumentType;

                        $translatableLocale = isset($livewire->activeLocale) ? $livewire->activeLocale : null;

                        return [
                            'parentDocumentTypeId' => $parentDocumentTypeId,
                            'translatableLocale' => $translatableLocale,
                            'parentContentId' => $this->getParentContentKey(),
                        ];
                    }),
            ])
            ->action(function (array $data, $livewire) {
                $url = \SolutionForest\InspireCms\Filament\Resources\Contents\Actions\CreateContentAction::generateCreateContentUrl(
                    documentType: $data['selection'],
                    parentContent: $this->getParentContentKey(),
                    translatableLocale: isset($livewire->activeLocale) ? $livewire->activeLocale : null
                );

                return redirect()->to($url);
            });
    }

    public function parentContentKey(Closure | string | int | null $parentContentKey): static
    {
        $this->parentContentKey = $parentContentKey;

        return $this;
    }

    public function parentDocumentType(Closure | string | int | Model | null $parentDocumentType): static
    {
        $this->parentDocumentType = $parentDocumentType;

        return $this;
    }

    public function documentTypeTitleUsing(Closure $callback): static
    {
        $this->documentTypeTitleUsing = $callback;

        return $this;
    }

    public function getParentContentKey(): null | string | int
    {
        return $this->evaluate($this->parentContentKey);
    }

    public function getParentDocumentType(): null | string | int | Model
    {
        return $this->evaluate($this->parentDocumentType);
    }

    public function nodeTitleUsing(Closure $callback): static
    {
        $this->nodeTitleUsing = $callback;

        return $this;
    }
}

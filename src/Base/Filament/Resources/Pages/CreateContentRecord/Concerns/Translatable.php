<?php

namespace SolutionForest\InspireCms\Base\Filament\Resources\Pages\CreateContentRecord\Concerns;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use LaraZeus\SpatieTranslatable\Resources\Concerns\HasActiveLocaleSwitcher;
use Livewire\Attributes\Locked;

/**
 * Override \Filament\Resources\Pages\CreateRecord\Concerns\Translatable
 */
trait Translatable
{
    use HasActiveLocaleSwitcher;

    protected ?string $oldActiveLocale = null;

    #[Locked]
    public $otherLocaleData = [];

    public function mountTranslatable(): void
    {
        // Set default locale if not set
        if (empty($this->activeLocale)) {
            $this->activeLocale = static::getResource()::getDefaultTranslatableLocale();
        }
    }

    public function getTranslatableLocales(): array
    {
        return static::getResource()::getTranslatableLocales();
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = app(static::getModel());

        $translatableAttributes = static::getResource()::getTranslatableAttributes();

        $record->fill(Arr::except($data, $translatableAttributes));

        foreach (Arr::only($data, $translatableAttributes) as $key => $value) {
            // Handle nested locale arrays (e.g., title.en, title.fr from Group with statePath)
            if (is_array($value) && $key === 'title') {
                foreach ($value as $locale => $localizedValue) {
                    $record->setTranslation($key, $locale, $localizedValue);
                }
            } else {
                $record->setTranslation($key, $this->activeLocale, $value);
            }
        }

        $originalData = $this->data;

        foreach ($this->otherLocaleData as $locale => $localeData) {
            $this->data = [
                ...$this->data,
                ...$localeData,
            ];

            try {
                $this->form->validate();
            } catch (ValidationException $exception) {
                continue;
            }

            $localeData = $this->mutateFormDataBeforeCreate($localeData);

            foreach (Arr::only($localeData, $translatableAttributes) as $key => $value) {
                // Handle nested locale arrays for otherLocaleData as well
                if (is_array($value) && $key === 'title') {
                    foreach ($value as $otherLoc => $localizedValue) {
                        $record->setTranslation($key, $otherLoc, $localizedValue);
                    }
                } else {
                    $record->setTranslation($key, $locale, $value);
                }
            }
        }

        $this->data = $originalData;

        if (
            static::getResource()::isScopedToTenant() &&
            ($tenant = Filament::getTenant())
        ) {
            return $this->associateRecordWithTenant($record, $tenant);
        }

        $record->save();

        return $record;
    }

    public function updatingActiveLocale(): void
    {
        $this->oldActiveLocale = $this->activeLocale;
    }

    public function updatedActiveLocale(string $newActiveLocale): void
    {
        if (blank($this->oldActiveLocale)) {
            return;
        }

        $this->resetValidation();

        $translatableAttributes = static::getResource()::getTranslatableAttributes();

        $this->otherLocaleData[$this->oldActiveLocale] = Arr::only($this->data, $translatableAttributes);

        $this->data = [
            ...Arr::except($this->data, $translatableAttributes),
            ...$this->otherLocaleData[$this->activeLocale] ?? [],
        ];

        unset($this->otherLocaleData[$this->activeLocale]);
    }
}

<?php

namespace SolutionForest\InspireCms\Filament\Tables\Actions;

use Closure;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\HtmlString;

class CloneAction extends Action
{
    use CanCustomizeProcess;

    protected ?Closure $saveRelationshipsUsing = null;

    protected array $replicateExcepts = [];

    public static function getDefaultName(): ?string
    {
        return 'clone';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn (): string => __('inspirecms::actions.clone.label'));

        $this->modalHeading(fn () => new HtmlString(__('inspirecms::actions.clone.modal.heading', ['label' => $this->getRecordTitle()])));

        $this->modalSubmitActionLabel(__('inspirecms::actions.clone.modal.actions.clone.label'));

        $this->successNotificationTitle(__('inspirecms::actions.clone.notifications.cloned.title'));

        $this->requiresConfirmation();

        $this->color('zinc');

        $this->icon(FilamentIcon::resolve('inspirecms::clone'));

        $this->modalIcon('heroicon-o-document-duplicate');

        $this->action(function (array $arguments, HasTable $livewire): void {

            $originalRecord = $this->getRecord();

            $record = $this->process(function (array $data) use ($originalRecord) {

                $record = $originalRecord->replicate($this->getReplicateExcepts());

                foreach ($data as $key => $value) {
                    $record->{$key} = $value;
                }

                $record->save();

                return $record;
            });

            $this->processSaveRelations([
                'originalRecord' => $originalRecord,
                'record' => $record,
            ]);

            $this->success();
        });
    }

    public function saveRelationshipsUsing(Closure $callback): static
    {
        $this->saveRelationshipsUsing = $callback;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    public function processSaveRelations(array $parameters = []): mixed
    {
        return $this->evaluate($this->saveRelationshipsUsing, $parameters);
    }

    public function replicateExcepts(array $excepts = []): static
    {
        $this->replicateExcepts = $excepts;

        return $this;
    }

    public function getReplicateExcepts(): array
    {
        return $this->replicateExcepts;
    }
}

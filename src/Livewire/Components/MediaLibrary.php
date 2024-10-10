<?php

namespace SolutionForest\InspireCms\Livewire\Components;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use SolutionForest\InspireCms\Support\InspireCmsConfig;

/**
 * @property Form $uploadFileForm
 */
class MediaLibrary extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    #[Url(as: 'p')]
    public string | int $parentKey;

    public null | string | int $selectedMediaId = null;

    public ?Model $selectedMedia = null;

    public ?array $uploadFileData = [];

    public function mount($parentKey = null)
    {
        $this->parentKey = $parentKey ?? static::getRootLevelParentId();
        $this->fillForm();
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [
            static::getRootLevelParentId() => 'Root',
        ];
        if ($this->parentKey == static::getRootLevelParentId()) {
            return $breadcrumbs;
        }

        $media = $this->getEloquentQuery()->find($this->parentKey);
        if ($media) {
            $breadcrumbs = array_merge($breadcrumbs, $media->ancestorsAndSelf()->mapWithKeys(fn ($item) => [
                $item->getKey() => $item->title,
            ])->all());

            return $breadcrumbs;
        }

        return [];
    }

    #[On('updatedSelectedMediaId')]
    public function updatedSelectedMediaId($value)
    {
        if ($value) {
            $this->selectedMedia = $this->getEloquentQuery()->find($value);
        } else {
            $this->selectedMedia = null;
        }
    }

    public function deleteMedia()
    {
        if ($this->selectedMediaId) {
            $media = $this->getEloquentQuery()->find($this->selectedMediaId);
            if ($media) {
                $media->delete();
            }
        }

        $this->selectedMediaId = null;
        $this->selectedMedia = null;
    }

    public function openFolder($mediaId = null)
    {
        $mediaId ??= $this->selectedMediaId;
        $this->selectedMediaId = null;
        $this->selectedMedia = null;
        $this->changeParent($mediaId);
    }

    public function changeParent($key)
    {
        if (blank($key) || $key == $this->parentKey) {
            return;
        }
        if ($key == static::getRootLevelParentId()) {
            $this->parentKey = $key;

            return;
        }
        $media = $this->getEloquentQuery()->find($key);
        if ($media && $media->isFolder()) {
            $this->parentKey = $key;

            return;
        }
    }

    //region Form
    public function uploadFileForm(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('files')
                    ->multiple(),
            ])
            ->statePath($this->getFormStatePathFor('uploadFileForm'));
    }

    public function saveUploadFile()
    {
        $files = $this->uploadFileData['files'] ?? [];
        if (empty($files)) {
            return;
        }

        foreach ($files as $file) {
            if (! $file instanceof TemporaryUploadedFile) {
                continue;
            }

            $this->createMediaFromUploadedFile($file);
        }

        $this->fillForm();
    }

    public function createFolderAction(): Action
    {
        return Action::make('createFolder')
            ->form([
                TextInput::make('title')->required(),
            ])
            ->successNotificationTitle('Folder created')
            ->action(function (array $data, Action $action) {
                if (empty($data['title'])) {
                    return;
                }
                $this->createMediaFolder($data['title']);
                $action->success();
            });
    }

    public function getFormStatePathFor(string $formName): ?string
    {
        return match ($formName) {
            'uploadFileForm' => 'uploadFileData',
            default => $this->getFormStatePath(),
        };
    }

    protected function getForms(): array
    {
        return [
            'uploadFileForm',
        ];
    }

    protected function fillForm(): void
    {
        $this->uploadFileForm->fill();
    }
    //endregion Form

    public function getMediaFromParent()
    {
        // ray($this->parentKey)->label(__FUNCTION__);
        return $this->getEloquentQuery()->with('media')->parent($this->parentKey)->get();
    }

    public function render()
    {
        return view('inspirecms::livewire.components.media-library', [
            'mediaItems' => $this->getMediaFromParent(),
        ]);
    }

    //region Helpers
    protected function createMediaFromUploadedFile(TemporaryUploadedFile $file): Model
    {
        $media = $this->getEloquentQuery()->create([
            'parent_id' => $this->parentKey,
            'title' => $file->getClientOriginalName(),
        ]);

        $media->addMedia($file)->toMediaCollection();

        return $media;
    }

    protected function createMediaFolder(string $title): Model
    {
        return $this->getEloquentQuery()->create([
            'parent_id' => $this->parentKey,
            'title' => $title,
            'is_folder' => true,
        ]);
    }

    protected function getEloquentQuery()
    {
        return static::getMediaAssetModel()::query();
    }

    protected static function getMediaAssetModel(): string
    {
        return InspireCmsConfig::getMediaAssetModelClass();
    }

    protected static function getRootLevelParentId(): string | int
    {
        return (new (static::getMediaAssetModel()))->getNestableRootValue();
    }
    //endregion Helpers
}

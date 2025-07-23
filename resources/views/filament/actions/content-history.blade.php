@livewire('inspirecms::content-version-history', [
    'ownerRecord' => $record,
    'pageClass' => $pageClass ?? get_class($this),
])
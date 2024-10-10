<?php

namespace SolutionForest\InspireCms\Models\Contracts;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

interface MediaAsset extends HasMedia
{
    public function registerMediaConversions(?Media $media = null): void;

    public function getFirstMedia(): ?Media;

    public function getUrl(string $conversionName = ''): ?string;

    public function getThumbnailUrl(): ?string;

    public function getThumbnail(): string;

    public function isImage(): bool;

    public function isFolder(): bool;
}

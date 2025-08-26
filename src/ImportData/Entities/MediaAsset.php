<?php

namespace SolutionForest\InspireCms\ImportData\Entities;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SolutionForest\InspireCms\Support\Models\Contracts\MediaAsset as MediaAssetContract;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaAsset extends BaseEntity
{
    protected static array $limitedProperties = [
        'id',
        'title',
        'nestable_id',
        'parent_id',
        'is_folder',
        'caption',
        'description',
        'author_type',
        'author_id',
        'created_at',
        'updated_at',
        'media_files',
        '__lft',
        '__rgt',
        '__parent_id',
        '__nestable_tree_id',
    ];

    public function __construct(
        public string $id,
        public string $title,
        public string|int|null $nestable_id,
        public ?string $parent_id = null,
        public bool $is_folder = false,
        public ?string $caption = null,
        public ?string $description = null,
        public ?string $author_type = null,
        public ?string $author_id = null,
        public ?array $media_files = null, // For storing media file data during export/import
        public ?\DateTimeInterface $created_at = null,
        public ?\DateTimeInterface $updated_at = null,
        // Nestable tree data for building hierarchy (prefixed with __ to avoid direct model updates)
        public ?int $__lft = null,
        public ?int $__rgt = null,
        public ?string $__parent_id = null,
        public ?string $__nestable_tree_id = null,
    ) {}

    public static function fromRecord($record): static
    {
        $mediaFiles = [];
        
        // Get all media files associated with this asset if it's a MediaAsset instance
        if ($record instanceof MediaAssetContract && method_exists($record, 'getMedia')) {
            $mediaCollection = $record->getMedia();
            if ($mediaCollection->isNotEmpty()) {
                foreach ($mediaCollection as $media) {
                    $mediaFiles[] = static::encodeMediaForExport($media);
                }
            }
        }

        // Get nestable tree data for hierarchy ordering
        $nestableTreeData = [
            '__lft' => null,
            '__rgt' => null,
            '__parent_id' => null,
            '__nestable_tree_id' => null,
        ];

        // Check if the record has nestable tree relationship
        if ($record && method_exists($record, 'nestableTree') && $record->nestableTree) {
            $nestableTree = $record->nestableTree;
            $nestableTreeData = [
                '__lft' => $nestableTree->_lft ?? null,
                '__rgt' => $nestableTree->_rgt ?? null,
                '__parent_id' => $nestableTree->parent_id ?? null,
                '__nestable_tree_id' => $nestableTree->id ?? null,
            ];
        }

        return new static(
            id: $record->id,
            title: $record->title,
            nestable_id: $record->nestable_id,
            parent_id: $record->parent_id,
            is_folder: $record->is_folder,
            caption: $record->caption,
            description: $record->description,
            author_type: $record->author_type,
            author_id: $record->author_id,
            media_files: $mediaFiles,
            created_at: $record->created_at,
            updated_at: $record->updated_at,
            __lft: $nestableTreeData['__lft'],
            __rgt: $nestableTreeData['__rgt'],
            __parent_id: $nestableTreeData['__parent_id'],
            __nestable_tree_id: $nestableTreeData['__nestable_tree_id'],
        );
    }

    /**
     * Encode media file and its responsive images for export
     * @param Media $media
     */
    protected static function encodeMediaForExport($media): array
    {
        // Try to map file path in export zip first
        $exportedFilePaths['__real__'] = "files/{$media->getKey()}/{$media->file_name}";

        // Try to map conversion files
        if (!empty($media->generated_conversions) && is_array($media->generated_conversions)) {
            foreach ($media->generated_conversions as $conversion => $generated) {
                if ($generated) {
                    $conversionFileName = pathinfo($media->file_name, PATHINFO_FILENAME) . '-' . $conversion . '.' . pathinfo($media->file_name, PATHINFO_EXTENSION);
                    $exportedFilePaths[$conversion] = "files/{$media->getKey()}/{$conversionFileName}";
                }
            }
        }

        // If file path cannot be mapped, fallback to encoding
        $encodedFiles['__real__'] = static::encodeFile($media);

        // Encode responsive images (try to map file path first, fallback to encode)
        if (!empty($media->generated_conversions) && is_array($media->generated_conversions)) {
            foreach ($media->generated_conversions as $conversion => $generated) {
                if ($generated) {
                    $encodedFiles[$conversion] = static::encodeConversionFile($media, $conversion);
                }
            }
        }

        return [
            'id' => $media->id,
            'uuid' => $media->uuid,
            'collection_name' => $media->collection_name,
            'name' => $media->name,
            'file_name' => $media->file_name,
            'mime_type' => $media->mime_type,
            'disk' => $media->disk,
            'conversions_disk' => $media->conversions_disk,
            'size' => $media->size,
            'manipulations' => $media->manipulations,
            'custom_properties' => $media->custom_properties,
            'generated_conversions' => $media->generated_conversions,
            'order_column' => $media->order_column,
            // Prefer file path, fallback to file_content
            '__exported_file_path' => $exportedFilePaths,
            '__encoded_files' => $encodedFiles
        ];
    }

    /**
     * Check if a string looks like a file path that should be encoded
     */
    protected static function shouldEncodeFile(string $path): bool
    {
        // Check if it's a relative path to an image file
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'];
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        return in_array($extension, $imageExtensions) && !Str::startsWith($path, 'http');
    }

    /**
     * Encode a media file for export
     */
    protected static function encodeFile($media)
    {
        if (!$media instanceof Media) {
            return null;
        }

        return static::encodeFileFromPath($media->disk, $media->getPathRelativeToRoot());
    }

    /**
     * Encode a conversion file for export
     */
    protected static function encodeConversionFile($media, $conversion): ?string
    {
        if (!$media instanceof Media) {
            return null;
        }
        return static::encodeFileFromPath($media->conversions_disk, $media->getPathRelativeToRoot($conversion));
    }

    /**
     * Encode a file from a specific disk and path
     * @param string $disk
     * @param string $path
     * @return string|null
     */
    private static function encodeFileFromPath($disk, $path)
    {
        try {
            $file = Storage::disk($disk)->get($path);
            return base64_encode($file);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function fromArray(array $data): MediaAsset
    {
        return new MediaAsset(
            id: $data['id'],
            title: $data['title'],
            nestable_id: $data['nestable_id'] ?? null,
            parent_id: $data['parent_id'] ?? null,
            is_folder: $data['is_folder'] ?? false,
            caption: $data['caption'] ?? null,
            description: $data['description'] ?? null,
            author_type: $data['author_type'] ?? null,
            author_id: $data['author_id'] ?? null,
            media_files: $data['media_files'] ?? null,
            created_at: isset($data['created_at']) ? new \DateTime($data['created_at']) : null,
            updated_at: isset($data['updated_at']) ? new \DateTime($data['updated_at']) : null,
            __lft: $data['__lft'] ?? null,
            __rgt: $data['__rgt'] ?? null,
            __parent_id: $data['__parent_id'] ?? null,
            __nestable_tree_id: $data['__nestable_tree_id'] ?? null,
        );
    }

    public function getDataForModel(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'parent_id' => $this->parent_id,
            'is_folder' => $this->is_folder,
            'caption' => $this->caption,
            'description' => $this->description,
            'author_type' => $this->author_type,
            'author_id' => $this->author_id,
        ];
    }


    /**
     * Map exported file paths for media assets from the extracted ZIP folder.
     *
     * @param \Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter $fs The filesystem instance
     * @param string $folderPath The folder path containing the media asset JSON files
     */
    public function mapExportedFilePathsForMediaAsset($fs, string $folderPath)
    {
        if (empty($this->media_files)) {
            return $this;
        }

        foreach ($this->media_files as &$mediaFile) {
            if (!empty($mediaFile['__exported_file_path']) && is_array($mediaFile['__exported_file_path'])) {
                foreach ($mediaFile['__exported_file_path'] as $conversionKey => $relativePath) {
                    // Build the full path within the extracted ZIP
                    $fullPath = $folderPath . '/' . ltrim($relativePath, '/');

                    // Check if the file actually exists in the extracted ZIP
                    if ($fs->exists($fullPath)) {
                        // Convert to absolute path on the filesystem
                        $absolutePath = $fs->path($fullPath);
                        
                        // Update the path to point to the actual extracted file
                        $mediaFile['__exported_file_path'][$conversionKey] = $absolutePath;
                    } else {
                        // File doesn't exist, remove this path option
                        unset($mediaFile['__exported_file_path'][$conversionKey]);
                    }
                }
                
                // If no valid paths remain, remove the __exported_file_path entirely
                if (empty($mediaFile['__exported_file_path'])) {
                    unset($mediaFile['__exported_file_path']);
                }
            }
        }

        return $this;
    }

    /**
     * Handle media import for a media asset.
     *
     * @param \Illuminate\Database\Eloquent\Model $model The media asset model
     * @return void
     */
    public function handleMediaImport($model)
    {
        // Skip media processing for folders
        if ($this->is_folder) {
            return;
        }

        // Clear existing media first
        $model->clearMediaCollection();

        // Create or update media (spatie) from $this->media_files (is array)
        if (!empty($this->media_files)) {
            foreach ($this->media_files as $mediaFileData) {
                $this->processMediaFile($model, $mediaFileData);
            }
        }
    }

    /**
     * Process a single media file for import.
     *
     * @param \Illuminate\Database\Eloquent\Model $model The media asset model
     * @param array $mediaFileData The media file data
     * @return void
     */
    protected function processMediaFile($model, array $mediaFileData): void
    {
        $fileName = $mediaFileData['file_name'] ?? null;
        $collectionName = $mediaFileData['collection_name'] ?? 'default';
        $mimeType = $mediaFileData['mime_type'] ?? 'application/octet-stream';
        $name = $mediaFileData['name'] ?? $fileName;

        if (empty($fileName)) {
            return;
        }

        // 2.1. Try zipped file from "__exported_file_path" first
        if (!empty($mediaFileData['__exported_file_path']) && is_array($mediaFileData['__exported_file_path'])) {

            if (isset($mediaFileData['__exported_file_path']['__real__']) && !empty($mediaFileData['__exported_file_path']['__real__']) && file_exists($mediaFileData['__exported_file_path']['__real__'])) {
                try {
                    $model
                        ->addMediaFromPath($mediaFileData['__exported_file_path']['__real__'])
                        ->setName($name)
                        ->setFileName($fileName)
                        ->toMediaCollection($collectionName);
                    return; // break the loop if successful
                } catch (\Exception $e) {
                    // If file path fails, continue to try encoded files
                }
            }
        }

        // 2.2. If not, try using "__encoded_files"
        if (!empty($mediaFileData['__encoded_files'])) {
            foreach ($mediaFileData['__encoded_files'] as $conversionKey => $encodedFile) {
                if ($conversionKey === '__real__' && !empty($encodedFile)) {
                    try {
                        // Decode base64 content
                        $decodedContent = base64_decode($encodedFile);
                        
                        if ($decodedContent !== false) {
                            $model
                                ->addMediaFromBase64(
                                    $encodedFile,
                                    [
                                        'mime_type' => $mimeType,
                                        'name' => $name
                                    ]
                                )
                                ->usingFileName($fileName)
                                ->toMediaCollection($collectionName);
                            return;
                        }
                    } catch (\Exception $e) {
                        // Continue to next encoded file if this one fails
                        continue;
                    }
                }
                // if (!empty($encodedFile)) {
                //     try {
                //         // Decode base64 content
                //         $decodedContent = base64_decode($encodedFile);
                        
                //         if ($decodedContent !== false) {
                //             $model
                //                 ->addMediaFromBase64(
                //                     $encodedFile,
                //                     [
                //                         'mime_type' => $mimeType,
                //                         'name' => $name
                //                     ]
                //                 )
                //                 ->usingFileName($fileName)
                //                 ->toMediaCollection($collectionName);
                //             return;
                //         }
                //     } catch (\Exception $e) {
                //         // Continue to next encoded file if this one fails
                //         continue;
                //     }
                // }
            }
        }

        // // Fallback: try legacy file_content field
        // if (!empty($mediaFileData['file_content'])) {
        //     try {
        //         $model
        //             ->addMediaFromBase64(
        //                 $mediaFileData['file_content'],
        //                 [
        //                     'mime_type' => $mimeType,
        //                     'name' => $name
        //                 ]
        //             )
        //             ->usingFileName($fileName)
        //             ->toMediaCollection($collectionName);
        //     } catch (\Exception $e) {
        //         // Log error or handle as needed
        //     }
        // }
    }
}

<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Stores and deletes project media on the public disk.
 *
 * Keeping this in one place means an image is never orphaned when a project is
 * updated or deleted: the same service handles storage and cleanup. Laravel
 * generates a random, collision-free filename for every upload, so a stored
 * file can never overwrite another project's image.
 */
class ProjectMediaService
{
    public function __construct(
        private string $disk = 'public',
    ) {}

    /**
     * Store an uploaded cover image and return its relative path.
     */
    public function storeCover(UploadedFile $file): string
    {
        return $file->store('projects/covers', $this->disk);
    }

    /**
     * Store an uploaded gallery image and return its relative path.
     */
    public function storeGalleryImage(UploadedFile $file): string
    {
        return $file->store('projects/gallery', $this->disk);
    }

    /**
     * Delete a stored file if it still exists.
     *
     * Only managed storage paths are ever touched: an absolute URL or a
     * site-root path points at an asset this service does not own, so it is
     * left alone rather than deleted from the wrong place.
     */
    public function delete(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        if (preg_match('#^(https?:)?//#i', $path) === 1 || str_starts_with($path, '/')) {
            return;
        }

        $disk = Storage::disk($this->disk);

        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }
}

<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Catalogue imagery uploaded from the back office.
 *
 * The catalogue was seeded with remote Unsplash URLs and the views only ever
 * read `url`, so an upload has to end up looking like any other URL. That is
 * the whole contract here: {@see store()} puts the file on the `public` disk
 * and hands back both the public URL (what the views render) and the disk path
 * (what lets us delete the file later). Rows with a null path are remote images
 * we do not own — {@see forget()} leaves those alone.
 */
class ImageStore
{
    public const DISK = 'public';

    /** Accepted by every image field in the back office. */
    public const RULES = ['image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'];

    /**
     * @return array{url: string, path: string}
     */
    public function store(UploadedFile $file, string $directory): array
    {
        $name = Str::random(24).'.'.strtolower($file->getClientOriginalExtension() ?: 'jpg');

        $path = $file->storeAs($directory, $name, ['disk' => self::DISK]);

        return [
            'url' => Storage::disk(self::DISK)->url($path),
            'path' => $path,
        ];
    }

    /**
     * Delete a file we own. Safe to call with null (a remote URL) or with a
     * path that has already gone.
     */
    public function forget(?string $path): void
    {
        if ($path) {
            Storage::disk(self::DISK)->delete($path);
        }
    }
}

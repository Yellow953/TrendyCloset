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
 *
 * Nothing is stored as it was uploaded. A phone photograph is a 4MB, 4032px,
 * sideways-EXIF JPEG, and the storefront asks for a 200px circle; so every
 * upload is straightened, optionally cropped to a fixed aspect, scaled down to
 * a sane maximum and re-encoded as WebP. The shop then serves one modest file
 * per image instead of the original — which is the difference between a card
 * grid that pops in and one that crawls.
 *
 * Re-encoding is best-effort: if GD cannot read the file (an exotic format, a
 * build without WebP), {@see store()} falls back to writing the upload as it
 * came. Losing bytes is a nuisance; losing the shopkeeper's photograph is not
 * acceptable.
 */
class ImageStore
{
    public const DISK = 'public';

    /** Accepted by every image field in the back office. */
    public const RULES = ['image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'];

    /** Aspect ratios worth naming. Pass one to {@see store()} to crop to it. */
    public const SQUARE = 1.0;

    /** WebP quality: the point where a garment photograph stops losing detail. */
    private const QUALITY = 82;

    /** Nothing on the storefront is rendered wider than this. */
    private const MAX_EDGE = 1600;

    /**
     * @param  string  $directory  path on the public disk, e.g. "products/12"
     * @param  float|null  $ratio  width ÷ height to crop to, centred; null keeps
     *                             the photograph's own shape
     * @param  int  $maxEdge  longest edge in pixels after scaling down
     * @return array{url: string, path: string}
     */
    public function store(UploadedFile $file, string $directory, ?float $ratio = null, int $maxEdge = self::MAX_EDGE): array
    {
        $encoded = $this->normalise($file, $ratio, $maxEdge);

        if ($encoded === null) {
            // Could not be re-encoded — keep the original rather than lose it.
            $name = Str::random(24).'.'.strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $path = $file->storeAs($directory, $name, ['disk' => self::DISK]);
        } else {
            $path = trim($directory, '/').'/'.Str::random(24).'.webp';
            Storage::disk(self::DISK)->put($path, $encoded);
        }

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

    /**
     * Straighten, crop, scale and re-encode to WebP.
     *
     * @return string|null the WebP bytes, or null if the file could not be read
     */
    private function normalise(UploadedFile $file, ?float $ratio, int $maxEdge): ?string
    {
        if (! function_exists('imagewebp') || ! function_exists('imagecreatefromstring')) {
            return null;
        }

        $bytes = @file_get_contents($file->getRealPath());
        if ($bytes === false) {
            return null;
        }

        $image = @imagecreatefromstring($bytes);
        if ($image === false) {
            return null;
        }

        $image = $this->deorient($image, $file);
        $image = $this->crop($image, $ratio);
        $image = $this->scale($image, $maxEdge);

        // WebP carries alpha; keep a cut-out PNG's transparency instead of
        // flattening it to black. imagescale/imagecrop hand back fresh images,
        // so the flags go on last.
        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        // GD writes to a stream, so capture it rather than round-trip a temp
        // file — the bytes are going straight to the disk adapter anyway.
        ob_start();
        $ok = imagewebp($image, null, self::QUALITY);
        $webp = (string) ob_get_clean();

        return $ok && $webp !== '' ? $webp : null;
    }

    /**
     * Apply the EXIF orientation a camera recorded instead of rotating pixels.
     * GD ignores it, so without this a portrait phone photo lands on its side.
     *
     * @param  \GdImage  $image
     * @return \GdImage
     */
    private function deorient($image, UploadedFile $file)
    {
        if (! function_exists('exif_read_data') || $file->getMimeType() !== 'image/jpeg') {
            return $image;
        }

        $exif = @exif_read_data($file->getRealPath());
        $orientation = (int) ($exif['Orientation'] ?? 0);

        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => null,
        };

        if ($rotated === null || $rotated === false) {
            return $image;
        }

        return $rotated;
    }

    /**
     * Centre-crop to an aspect ratio: take the biggest window of that shape the
     * photograph contains, from the middle. The middle is where the garment is
     * in every catalogue shot; cropping from a corner is how you behead a model.
     *
     * @param  \GdImage  $image
     * @return \GdImage
     */
    private function crop($image, ?float $ratio)
    {
        if ($ratio === null || $ratio <= 0) {
            return $image;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        // Already that shape, near enough that cropping would only cost pixels.
        if (abs(($width / $height) - $ratio) < 0.01) {
            return $image;
        }

        if ($width / $height > $ratio) {
            $cropWidth = (int) round($height * $ratio);
            $cropHeight = $height;
        } else {
            $cropWidth = $width;
            $cropHeight = (int) round($width / $ratio);
        }

        $cropped = imagecrop($image, [
            'x' => intdiv($width - $cropWidth, 2),
            'y' => intdiv($height - $cropHeight, 2),
            'width' => $cropWidth,
            'height' => $cropHeight,
        ]);

        if ($cropped === false) {
            return $image;
        }

        return $cropped;
    }

    /**
     * Scale down so the longest edge is at most $maxEdge. Never scales up — a
     * small photograph is a small photograph, and stretching it only adds bytes.
     *
     * @param  \GdImage  $image
     * @return \GdImage
     */
    private function scale($image, int $maxEdge)
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $longest = max($width, $height);

        if ($longest <= $maxEdge) {
            return $image;
        }

        $factor = $maxEdge / $longest;
        $resized = imagescale($image, (int) round($width * $factor), (int) round($height * $factor));

        if ($resized === false) {
            return $image;
        }

        return $resized;
    }
}

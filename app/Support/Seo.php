<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Per-request page metadata: the one place a page's title, description, social
 * card, canonical URL, indexability and JSON-LD graph are assembled.
 *
 * Controllers configure it (`$this->seo->page(...)`) and `partials/seo.blade.php`
 * renders it — views never hand-roll meta tags. It is a scoped binding, so the
 * header composer and the layout read the same instance the controller wrote.
 *
 * Defaults are always safe: a page that says nothing still emits the store's
 * default title, description, image and a self-referencing canonical.
 */
class Seo
{
    private ?string $title = null;

    private ?string $description = null;

    private ?string $image = null;

    private ?string $canonical = null;

    /** Open Graph object type — `website`, `product`, `article`. */
    private string $type = 'website';

    private bool $index = true;

    /** @var array<int, array<string, mixed>> JSON-LD nodes for the @graph. */
    private array $schema = [];

    /**
     * Set the page title and description in one call — what most actions need.
     */
    public function page(string $title, ?string $description = null): static
    {
        $this->title($title);

        if ($description !== null) {
            $this->description($description);
        }

        return $this;
    }

    public function title(string $title): static
    {
        $this->title = trim($title);

        return $this;
    }

    /**
     * Descriptions are cleaned and clipped here rather than at every call site:
     * product copy is free text and may carry markup or run long.
     */
    public function description(?string $description): static
    {
        $description = trim(preg_replace('/\s+/', ' ', strip_tags((string) $description)));

        $this->description = $description === '' ? null : Str::limit($description, 155);

        return $this;
    }

    /**
     * The social card image. Relative paths are resolved against the app URL so
     * every og:image is absolute, as the crawlers require.
     */
    public function image(?string $image): static
    {
        $this->image = $image ? $this->absolute($image) : null;

        return $this;
    }

    public function canonical(string $url): static
    {
        $this->canonical = $url;

        return $this;
    }

    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Keep the page out of the index while still letting crawlers follow its
     * links — the right setting for the bag, checkout and filtered listings.
     */
    public function noindex(): static
    {
        $this->index = false;

        return $this;
    }

    /**
     * Push one or more JSON-LD nodes onto the page's @graph. Nulls are ignored
     * so builders can return null when they have nothing meaningful to say.
     *
     * @param  array<string, mixed>|null  ...$nodes
     */
    public function schema(?array ...$nodes): static
    {
        foreach ($nodes as $node) {
            if ($node) {
                $this->schema[] = $node;
            }
        }

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Rendering — read by partials/seo.blade.php
    |--------------------------------------------------------------------------
    */

    /**
     * The full <title>: the page title with the brand appended, unless the page
     * already names the brand (the home page does).
     */
    public function metaTitle(): string
    {
        $brand = config('seo.brand');
        $title = $this->title ?: config('seo.brand_full');

        return Str::contains($title, $brand)
            ? $title
            : $title.config('seo.separator').$brand;
    }

    public function metaDescription(): string
    {
        return $this->description ?: config('seo.description');
    }

    public function metaImage(): string
    {
        return $this->image ?: $this->absolute(config('seo.image'));
    }

    public function metaCanonical(): string
    {
        // Default to the current path *without* the query string: filters and
        // tracking params must never mint a second canonical for one page.
        return $this->canonical ?: url()->current();
    }

    public function metaRobots(): string
    {
        return $this->index
            ? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1'
            : 'noindex, follow';
    }

    public function ogType(): string
    {
        return $this->type;
    }

    /**
     * The page's JSON-LD, always including the site-wide Organization and
     * WebSite nodes so every URL is self-describing to a crawler that lands
     * on it cold — which is how generative engines usually arrive.
     *
     * @return array<string, mixed>
     */
    public function jsonLd(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@graph' => array_values(array_merge(
                [Schema::organization(), Schema::website()],
                $this->schema
            )),
        ];
    }

    /**
     * Absolute-ise a path, leaving already-absolute URLs (product imagery lives
     * on Unsplash) untouched.
     */
    private function absolute(string $path): string
    {
        return Str::startsWith($path, ['http://', 'https://']) ? $path : asset($path);
    }
}

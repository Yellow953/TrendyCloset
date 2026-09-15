<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * One line of the header's pre-header bar, managed from the back office.
 * Several can be live at once — the bar rotates through them, see
 * initAnnouncements() in app.js — rather than picking a single winner.
 */
class Announcement extends Model
{
    protected $fillable = [
        'message',
        'link_label',
        'link_url',
        'position',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Where the link goes. A message with no link of its own still gets
     * one — the bar always points somewhere.
     */
    public function linkUrl(): string
    {
        return $this->link_url ?: route('listing');
    }

    /**
     * @param  Builder<Announcement>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<Announcement>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('position')->orderBy('id');
    }

    /**
     * The bar can never be blank: with nothing published it falls back to the
     * shop's standing delivery promise, same text the bar always used to show.
     */
    public static function fallback(): self
    {
        return new self([
            'message' => 'Delivery across '.config('store.contact.country').' in 1–3 working days',
            'link_label' => 'Shop now',
            'link_url' => route('listing'),
        ]);
    }

    /**
     * @return Collection<int, Announcement>
     */
    public static function forHeader(): Collection
    {
        $live = static::query()->active()->ordered()->get();

        return $live->isNotEmpty() ? $live : collect([static::fallback()]);
    }
}

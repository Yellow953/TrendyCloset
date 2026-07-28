<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * One slide of the rotating home-page hero, managed from the back office.
 *
 * The copy is split the way the design reads it: an `eyebrow` above, a `title`
 * line, an `accent` line set in italic serif beneath it, then a paragraph and
 * a button.
 */
class HeroSlide extends Model
{
    protected $fillable = [
        'eyebrow',
        'title',
        'accent',
        'copy',
        'cta_label',
        'cta_url',
        'image_url',
        'image_path',
        'image_credit',
        'image_credit_href',
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
     * Where the button goes. A slide with no link still renders as a slide —
     * the button is simply pointed at the shop rather than nowhere.
     */
    public function linkUrl(): string
    {
        return $this->cta_url ?: route('listing');
    }

    /**
     * @param  Builder<HeroSlide>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<HeroSlide>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('position')->orderBy('id');
    }

    /**
     * The three slides the shop shipped with. They seed an empty table, and
     * they are what the home page falls back to when every slide has been
     * unpublished — the hero is a 640px band, so it can never render blank.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function defaults(): array
    {
        $photo = fn (string $id) => "https://images.unsplash.com/{$id}?q=60&w=1400&h=1200&auto=format&fit=crop";

        return [
            [
                'eyebrow' => 'TRENDY CLOSET · SUMMER '.now()->year,
                'title' => 'Wear the pieces',
                'accent' => 'everyone asks about',
                'copy' => 'Hand-picked by Leila and styled before it ever ships. New drops every Friday.',
                'cta_label' => 'Shop New In',
                'cta_url' => '/shop?edit=new',
                'image_url' => $photo('photo-1552374196-1ab2a1c593e8'),
                'image_credit' => 'Photo via Unsplash',
                'image_credit_href' => 'https://unsplash.com',
                'position' => 0,
                'is_active' => true,
            ],
            [
                'eyebrow' => 'THE SALE IS LIVE',
                'title' => 'Up to 40% off',
                'accent' => 'your summer favourites',
                'copy' => 'Marked-down pieces from every section — while sizes last.',
                'cta_label' => 'Shop Sale',
                'cta_url' => '/shop?edit=sale',
                'image_url' => $photo('photo-1558769132-cb1aea458c5e'),
                'image_credit' => 'Photo via Unsplash',
                'image_credit_href' => 'https://unsplash.com',
                'position' => 1,
                'is_active' => true,
            ],
            [
                'eyebrow' => "LEILA'S PICKS",
                'title' => 'Styled by Leila,',
                'accent' => 'worn by you',
                'copy' => 'The edit she keeps restyling — the pieces that go with everything.',
                'cta_label' => 'Shop the edit',
                'cta_url' => '/shop?edit=featured',
                'image_url' => $photo('photo-1523381210434-271e8be1f52b'),
                'image_credit' => 'Photo via Unsplash',
                'image_credit_href' => 'https://unsplash.com',
                'position' => 2,
                'is_active' => true,
            ],
        ];
    }
}

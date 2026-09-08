<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class ProductVariant extends Model
{
    /** @use HasFactory<\Database\Factories\ProductVariantFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'size',
        'color',
        'price_override',
        'stock',
        'is_active',
    ];

    /** The letter run, in the order a rail reads. */
    private const LETTER_SIZES = ['XXS', 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'];

    /**
     * Every size the admin size select offers: the letter run, common denim
     * waist sizes, and "One Size" for pieces sized as a single piece.
     */
    public const SIZES = [...self::LETTER_SIZES, 'One Size', '24', '26', '28', '30', '32', '34', '36', '38', '40'];

    protected function casts(): array
    {
        return [
            'price_override' => 'decimal:2',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Product, ProductVariant>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Price to charge for this variant — its override, else the product price.
     */
    public function getEffectivePriceAttribute(): string
    {
        return $this->price_override ?? $this->product->price;
    }

    public function getInStockAttribute(): bool
    {
        return $this->stock > 0;
    }

    /**
     * Human label for a variant line in the bag, e.g. "Size M · Sage".
     */
    public function getLabelAttribute(): string
    {
        return collect([
            $this->size ? 'Size '.$this->size : null,
            $this->color,
        ])->filter()->implode(' · ');
    }

    /**
     * Apparel sizes do not sort alphabetically ("L" before "M" before "S" is
     * nonsense) and waist sizes are numeric strings. Order by the canonical
     * run, then numerically, so filter chips read the way a rail does.
     *
     * @param  Collection<int, string>  $sizes
     * @return Collection<int, string>
     */
    public static function sortSizes(Collection $sizes): Collection
    {
        $order = self::LETTER_SIZES;

        return $sizes
            ->sortBy(function (string $size) use ($order) {
                if (is_numeric($size)) {
                    return [1, (float) $size];
                }

                $rank = array_search(strtoupper($size), $order, true);

                return [0, $rank === false ? 99 : $rank];
            })
            ->values();
    }
}

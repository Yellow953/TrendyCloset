<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image_url',
        'image_path',
        'image_credit',
        'image_credit_href',
        'position',
        'is_active',
    ];

    /**
     * Cached by {@see selfAndDescendantIds()} for the life of the instance.
     *
     * @var array<int, int>|null
     */
    private ?array $descendantIds = null;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Category, Category>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * @return HasMany<Category>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('position');
    }

    /**
     * @return HasMany<Product>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function isParent(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * This category's id plus every id beneath it. Products are filed against
     * leaves, so browsing a parent has to widen to its children to find any.
     *
     * Walked a level at a time rather than a node at a time: recursing through
     * the `children` relation cost one query per child, so a section with five
     * subcategories spent six queries working out six ids. Memoised on top of
     * that, because a listing asks three times over — the facet scope, the
     * product scope and the meta description.
     *
     * @return array<int, int>
     */
    public function selfAndDescendantIds(): array
    {
        if ($this->descendantIds !== null) {
            return $this->descendantIds;
        }

        $ids = [$this->id];
        $level = [$this->id];

        while ($level !== []) {
            $level = static::query()->whereIn('parent_id', $level)->pluck('id')->all();
            $ids = array_merge($ids, $level);
        }

        return $this->descendantIds = $ids;
    }

    /**
     * @param  Builder<Category>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Ordered as merchandised.
     *
     * @param  Builder<Category>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('position')->orderBy('name');
    }

    /**
     * Top-level categories only (no parent).
     *
     * @param  Builder<Category>  $query
     */
    public function scopeRoots(Builder $query): void
    {
        $query->whereNull('parent_id');
    }
}

<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Shared reads of the merchandising taxonomy — the navigation tree and the
 * product tallies hung off it. Resolved once per request (scoped binding) so
 * the header, the listing sidebar and the home carousel share one set of
 * queries instead of each running their own.
 */
class Catalog
{
    /** @var Collection<int, Category>|null */
    private ?Collection $tree = null;

    /** @var array<int, int>|null */
    private ?array $counts = null;

    private ?Product $spotlight = null;

    /**
     * Active root categories with their active children, in merchandised order.
     *
     * @return Collection<int, Category>
     */
    public function tree(): Collection
    {
        return $this->tree ??= Category::query()
            ->active()
            ->roots()
            ->ordered()
            ->with(['children' => fn ($q) => $q->active()->ordered()])
            ->get();
    }

    /**
     * Every category in the tree, parents followed by their children — the
     * flat form the "Shop by Category" carousel and the mobile drawer want.
     *
     * @return Collection<int, Category>
     */
    public function flat(): Collection
    {
        return $this->tree()->flatMap(fn (Category $c) => [$c, ...$c->children]);
    }

    /**
     * Active product counts keyed by category id. A parent's count includes
     * everything filed under its children, since products live on the leaves.
     *
     * @return array<int, int>
     */
    public function counts(): array
    {
        if ($this->counts !== null) {
            return $this->counts;
        }

        $direct = Product::query()
            ->active()
            ->selectRaw('category_id, COUNT(*) as aggregate')
            ->groupBy('category_id')
            ->pluck('aggregate', 'category_id');

        $counts = [];

        foreach ($this->tree() as $root) {
            $total = (int) ($direct[$root->id] ?? 0);

            foreach ($root->children as $child) {
                $counts[$child->id] = (int) ($direct[$child->id] ?? 0);
                $total += $counts[$child->id];
            }

            $counts[$root->id] = $total;
        }

        return $this->counts = $counts;
    }

    public function countFor(Category $category): int
    {
        return $this->counts()[$category->id] ?? 0;
    }

    /**
     * The product the mega-menu puts a picture to — the newest featured piece
     * that actually has imagery.
     */
    public function spotlight(): ?Product
    {
        return $this->spotlight ??= Product::query()
            ->active()
            ->featured()
            ->whereHas('images')
            ->with('images')
            ->orderByDesc('id')
            ->first();
    }
}

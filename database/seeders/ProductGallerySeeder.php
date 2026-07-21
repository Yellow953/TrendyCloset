<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Gives every product a small gallery so the product page has a thumbnail rail
 * to work with. The demo catalogue ships one primary photo per product; the
 * extra frames are drawn from a pool belonging to the product's root category,
 * so a pair of jeans never shows a coat as its "second view".
 *
 * Idempotent: products that already have more than one image are skipped, so
 * this can be re-run over an existing database.
 */
class ProductGallerySeeder extends Seeder
{
    /** How many images a product ends up with, primary included. */
    private const TARGET = 3;

    /**
     * Extra photo ids per root category slug. Every id here is already used
     * elsewhere in the demo catalogue, so all of them are known to resolve.
     *
     * @var array<string, array<int, string>>
     */
    private array $pools = [
        'jeans' => [
            'photo-1541099649105-f69ad21f3246',
            'photo-1475178626620-a4d074967452',
            'photo-1584370848010-d7fe6bc767ec',
            'photo-1542272604-787c3835535d',
            'photo-1544022613-e87ca75a784a',
        ],
        'winter-section' => [
            'photo-1548036328-c9fa89d128fa',
            'photo-1490481651871-ab68de25d43d',
            'photo-1539533018447-63fcce2678e3',
            'photo-1591047139829-d91aecb6caea',
            'photo-1576871337622-98d48d1cf531',
        ],
        'summer-section' => [
            'photo-1515372039744-b8f02a3ae446',
            'photo-1572804013309-59a88b7e92f1',
            'photo-1594633312681-425c7b97ccd1',
            'photo-1622470953794-aa9c70b0fb9d',
            'photo-1618354691373-d851c5c3a990',
        ],
        'shirts' => [
            'photo-1596755094514-f87e34085b2c',
            'photo-1487222477894-8943e31ef7b2',
            'photo-1619086303291-0ef7699e4b31',
        ],
        'pants' => [
            'photo-1627577279497-4b24bf1021b6',
            'photo-1591369822096-ffd140ec948f',
            'photo-1604176354204-9268737828e4',
        ],
        'shorts-skirts' => [
            'photo-1620012253295-c15cc3e65df4',
            'photo-1583496661160-fb5886a0aaaa',
            'photo-1495121605193-b116b5b9c5fe',
        ],
    ];

    public function run(): void
    {
        $products = Product::with(['images', 'category.parent'])->get();

        foreach ($products as $product) {
            if ($product->images->count() >= self::TARGET) {
                continue;
            }

            $pool = $this->poolFor($product);

            if ($pool === []) {
                continue;
            }

            // Never repeat a frame the product already shows.
            $existing = $product->images->pluck('url')->all();
            $position = (int) $product->images->max('position') + 1;

            foreach ($pool as $id) {
                if ($product->images->count() + ($position - 1) >= self::TARGET) {
                    break;
                }

                $url = $this->url($id);

                if (in_array($url, $existing, true)) {
                    continue;
                }

                $product->images()->create([
                    'url' => $url,
                    'credit' => 'Photo via Unsplash',
                    'credit_href' => 'https://unsplash.com',
                    'is_primary' => false,
                    'position' => $position++,
                ]);

                $existing[] = $url;
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function poolFor(Product $product): array
    {
        $category = $product->category;

        if (! $category) {
            return [];
        }

        $rootSlug = $category->parent_id
            ? (Category::find($category->parent_id)?->slug ?? $category->slug)
            : $category->slug;

        return $this->pools[$rootSlug] ?? [];
    }

    private function url(string $id): string
    {
        return "https://images.unsplash.com/{$id}?q=60&w=1000&auto=format&fit=crop";
    }
}

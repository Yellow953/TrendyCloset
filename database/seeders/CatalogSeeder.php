<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds the storefront taxonomy (parent categories + subcategories) and a
 * matching demo catalogue. Imagery is served from Unsplash; every photo id
 * below was validated as reachable before seeding.
 */
class CatalogSeeder extends Seeder
{
    /**
     * Real Unsplash photographer credits for the photo ids we can attribute
     * (carried over from the original design doc). Ids not listed here fall
     * back to a neutral "Photo via Unsplash" credit rather than a fabricated
     * name. Keyed by photo id → [author, username].
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private array $credits = [
        'photo-1490481651871-ab68de25d43d' => ['Priscilla Du Preez', 'priscilladupreez'],
        'photo-1619086303291-0ef7699e4b31' => ['Vladimir Yelizarov', 'yelizarov'],
        'photo-1627577279497-4b24bf1021b6' => ['Helen Ast', 'helenaast'],
        'photo-1495121605193-b116b5b9c5fe' => ['Alexandra Gorn', 'alexagorn'],
    ];

    /**
     * Build an Unsplash image descriptor, attributing the photographer when known.
     */
    private function img(string $id, int $w = 900): array
    {
        [$author, $slug] = $this->credits[$id] ?? [null, null];

        return [
            'url' => "https://images.unsplash.com/{$id}?q=60&w={$w}&auto=format&fit=crop",
            'credit' => $author ? "Photo by {$author} on Unsplash" : 'Photo via Unsplash',
            'credit_href' => $slug ? "https://unsplash.com/@{$slug}" : 'https://unsplash.com',
        ];
    }

    public function run(): void
    {
        $cats = $this->seedCategories();
        $this->seedProducts($cats);
        $this->seedCoupons();

        // Fills out each product's gallery; safe to re-run on its own.
        $this->call(ProductGallerySeeder::class);
    }

    /**
     * The navigation taxonomy. Each entry is [name, image id, [children...]],
     * where a child is [name, image id]. Subcategory slugs are namespaced by
     * their parent so duplicate leaf names (Tops, Sets, Wide legs) stay unique.
     *
     * @return array<string, Category> map of slug => Category (parents + leaves)
     */
    private function seedCategories(): array
    {
        $tree = [
            ['Jeans', 'photo-1542272604-787c3835535d', [
                ['Wide legs', 'photo-1475178626620-a4d074967452'],
                ['Straight', 'photo-1541099649105-f69ad21f3246'],
                ['Flare', 'photo-1544022613-e87ca75a784a'],
                ['Mom', 'photo-1583743814966-8936f5b7be1a'],
                ['Skinny', 'photo-1584370848010-d7fe6bc767ec'],
            ]],
            ['Winter Section', 'photo-1591047139829-d91aecb6caea', [
                ['Tops', 'photo-1602810318383-e386cc2a3ccf'],
                ['Sweaters', 'photo-1548036328-c9fa89d128fa'],
                ['Cardigans', 'photo-1490481651871-ab68de25d43d'],
                ['Sets', 'photo-1576871337622-98d48d1cf531'],
                ['Coats & Jackets', 'photo-1539533018447-63fcce2678e3'],
                ['Vest', 'photo-1644140821708-bd32b2796e39'],
            ]],
            ['Summer Section', 'photo-1594633312681-425c7b97ccd1', [
                ['Basics', 'photo-1515372039744-b8f02a3ae446'],
                ['Tops', 'photo-1618354691373-d851c5c3a990'],
                ['Sets', 'photo-1622470953794-aa9c70b0fb9d'],
                ['Dresses', 'photo-1572804013309-59a88b7e92f1'],
                ['Vest', 'photo-1525171254930-643fc658b64e'],
            ]],
            ['Shirts', 'photo-1770294758906-c8762abb2c8b', []],
            ['Pants', 'photo-1627577279497-4b24bf1021b6', [
                ['Mom cut', 'photo-1591369822096-ffd140ec948f'],
                ['Wide legs', 'photo-1604176354204-9268737828e4'],
            ]],
            ['Shorts & Skirts', 'photo-1639818023520-ab4c21c5892b', []],
            ['Pyjamas', 'photo-1768696082915-bdc6774f94c7', []],
            ['Scarf', 'photo-1571498521264-5fcaa7f8edf4', []],
            ['Accessories', 'photo-1492707892479-7bc8d5a4ee93', []],
        ];

        $cats = [];
        $pos = 0;

        foreach ($tree as [$name, $imgId, $children]) {
            $parentSlug = Str::slug($name);
            $parent = Category::create([
                'name' => $name,
                'slug' => $parentSlug,
                'position' => $pos++,
                'is_active' => true,
            ] + $this->imageColumns($imgId));
            $cats[$parentSlug] = $parent;

            $childPos = 0;
            foreach ($children as [$childName, $childImgId]) {
                $childSlug = $parentSlug.'-'.Str::slug($childName);
                $cats[$childSlug] = Category::create([
                    'parent_id' => $parent->id,
                    'name' => $childName,
                    'slug' => $childSlug,
                    'position' => $childPos++,
                    'is_active' => true,
                ] + $this->imageColumns($childImgId));
            }
        }

        return $cats;
    }

    /**
     * @return array<string, string> image_url / image_credit / image_credit_href
     */
    private function imageColumns(string $imgId): array
    {
        $img = $this->img($imgId, 900);

        return [
            'image_url' => $img['url'],
            'image_credit' => $img['credit'],
            'image_credit_href' => $img['credit_href'],
        ];
    }

    /**
     * @param  array<string, Category>  $cats
     */
    private function seedProducts(array $cats): void
    {
        // leaf slug, name, price, compare (nullable), badge, rating, featured, deal, image id
        $products = [
            // Jeans
            ['jeans-wide-legs', 'Wide Leg Jeans — Ecru', 58, 72, '-20%', 5, true, true, 'photo-1475178626620-a4d074967452'],
            ['jeans-straight', 'Straight Leg Jeans — Indigo', 54, null, 'NEW', 5, true, false, 'photo-1541099649105-f69ad21f3246'],
            ['jeans-flare', 'Flare Jeans — Vintage Blue', 60, null, null, 4, false, false, 'photo-1544022613-e87ca75a784a'],
            ['jeans-mom', 'Mom Jeans — Light Wash', 52, 64, '-18%', 5, false, true, 'photo-1583743814966-8936f5b7be1a'],
            ['jeans-skinny', 'Skinny Jeans — Black', 48, null, null, 4, true, false, 'photo-1584370848010-d7fe6bc767ec'],

            // Winter Section
            ['winter-section-tops', 'Ribbed Winter Top — Charcoal', 34, null, 'NEW', 4, false, false, 'photo-1602810318383-e386cc2a3ccf'],
            ['winter-section-sweaters', 'Chunky Knit Sweater — Oat', 56, 68, '-18%', 5, true, true, 'photo-1548036328-c9fa89d128fa'],
            ['winter-section-cardigans', 'Longline Cardigan — Camel', 62, null, null, 5, true, false, 'photo-1490481651871-ab68de25d43d'],
            ['winter-section-sets', 'Knit Lounge Set — Grey', 72, null, 'NEW', 5, false, false, 'photo-1576871337622-98d48d1cf531'],
            ['winter-section-coats-jackets', 'Wool Blend Coat — Camel', 128, 160, '-20%', 5, true, true, 'photo-1539533018447-63fcce2678e3'],
            ['winter-section-coats-jackets', 'Padded Puffer Jacket — Black', 98, null, 'NEW', 4, false, false, 'photo-1608063615781-e2ef8c73d114'],
            ['winter-section-vest', 'Padded Puffer Vest — Forest', 64, null, 'NEW', 5, true, false, 'photo-1644140821708-bd32b2796e39'],
            ['winter-section-vest', 'Quilted Vest — Black', 58, 72, '-19%', 4, false, true, 'photo-1577910473180-55de54e7ff30'],

            // Summer Section
            ['summer-section-basics', 'Cotton Basics Tee — White', 18, null, null, 4, false, false, 'photo-1515372039744-b8f02a3ae446'],
            ['summer-section-basics', 'Ribbed Tank — Sage', 16, null, 'NEW', 4, false, false, 'photo-1618354691373-d851c5c3a990'],
            ['summer-section-tops', 'Linen Summer Top — Ecru', 38, null, 'NEW', 5, true, false, 'photo-1619086303291-0ef7699e4b31'],
            ['summer-section-sets', 'Two-Piece Summer Set — Terracotta', 64, 78, '-18%', 5, true, true, 'photo-1622470953794-aa9c70b0fb9d'],
            ['summer-section-dresses', 'Floral Midi Dress — Blush', 58, null, 'NEW', 5, true, false, 'photo-1572804013309-59a88b7e92f1'],
            ['summer-section-dresses', 'Satin Slip Dress — Champagne', 54, 66, '-18%', 5, false, true, 'photo-1594633312681-425c7b97ccd1'],
            ['summer-section-vest', 'Ribbed Cami Vest — Black', 22, null, 'NEW', 4, false, false, 'photo-1525171254930-643fc658b64e'],

            // Shirts (no subcategories)
            ['shirts', 'Oversized Poplin Shirt — White', 44, null, 'NEW', 5, true, false, 'photo-1611235116156-0cbda6649efb'],
            ['shirts', 'Checked Flannel Shirt — Rust', 42, null, null, 4, false, false, 'photo-1610886886310-be2828d6afd5'],
            ['shirts', 'Relaxed Linen Shirt — Sand', 46, 56, '-18%', 4, false, true, 'photo-1619086303291-0ef7699e4b31'],

            // Pants
            ['pants-mom-cut', 'Mom Cut Trousers — Khaki', 50, null, 'NEW', 4, true, false, 'photo-1591369822096-ffd140ec948f'],
            ['pants-wide-legs', 'Wide Leg Trousers — Black', 54, 66, '-18%', 5, true, true, 'photo-1627577279497-4b24bf1021b6'],

            // Shorts & Skirts (no subcategories)
            ['shorts-skirts', 'Denim Shorts — Light Wash', 36, null, 'NEW', 4, true, false, 'photo-1591195853828-11db59a44f6b'],
            ['shorts-skirts', 'Pleated Mini Skirt — Camel', 40, 50, '-20%', 5, false, true, 'photo-1583496661160-fb5886a0aaaa'],
            ['shorts-skirts', 'Satin Midi Skirt — Champagne', 48, null, null, 5, false, false, 'photo-1495121605193-b116b5b9c5fe'],

            // Pyjamas (no subcategories)
            ['pyjamas', 'Cotton Pyjama Set — Sky', 42, null, 'NEW', 5, true, false, 'photo-1768696082915-bdc6774f94c7'],
            ['pyjamas', 'Printed Satin Pyjamas — Crimson', 56, 68, '-18%', 5, false, true, 'photo-1654512462970-8191bc8742bd'],

            // Scarf (no subcategories)
            ['scarf', 'Woven Winter Scarf — Sage', 28, null, 'NEW', 5, true, false, 'photo-1457545195570-67f207084966'],
            ['scarf', 'Lightweight Scarf — Olive', 24, 30, '-20%', 4, false, true, 'photo-1643312892626-80632ef6d7dc'],

            // Accessories (no subcategories)
            ['accessories', 'Statement Earrings — Gold', 18, null, 'NEW', 5, true, false, 'photo-1549439602-43ebca2327af'],
            ['accessories', 'Structured Handbag — Powder Blue', 68, 84, '-19%', 5, false, true, 'photo-1575202332411-b01fe9ace7a8'],
        ];

        $slugSeen = [];

        foreach ($products as [$catSlug, $name, $price, $compare, $badge, $rating, $featured, $deal, $imgId]) {
            $color = str_contains($name, '—') ? trim(Str::afterLast($name, '—')) : null;

            // Guarantee a unique product slug even if names ever collide.
            $slug = Str::slug($name);
            if (isset($slugSeen[$slug])) {
                $slug .= '-'.(++$slugSeen[$slug]);
            } else {
                $slugSeen[$slug] = 1;
            }

            $product = Product::create([
                'category_id' => $cats[$catSlug]->id,
                'name' => $name,
                'slug' => $slug,
                'description' => 'A Trendy Closet staple, styled by Pamela.',
                'price' => $price,
                'compare_at_price' => $compare,
                'badge' => $badge,
                'rating' => $rating,
                'is_featured' => $featured,
                'is_active' => true,
                // Deal of the Week countdown target (~4d 16h out), matching the design.
                'sale_ends_at' => $deal ? now()->addDays(4)->addHours(16) : null,
            ]);

            $product->images()->create($this->img($imgId, 1000) + ['is_primary' => true, 'position' => 0]);

            $this->seedVariants($product, $cats[$catSlug], $color);
        }
    }

    private function seedVariants(Product $product, Category $category, ?string $color): void
    {
        // Bottoms use waist sizes; everything else uses standard apparel sizing.
        $rootSlug = $category->parent_id
            ? Category::find($category->parent_id)->slug
            : $category->slug;

        $sizes = match ($rootSlug) {
            'jeans', 'pants', 'shorts-skirts' => ['24', '26', '28', '30', '32', '34'],
            'scarf', 'accessories' => ['One Size'],
            default => ['XS', 'S', 'M', 'L', 'XL', '2XL'],
        };

        $base = strtoupper(Str::slug($product->name));

        foreach ($sizes as $size) {
            $product->variants()->create([
                'sku' => $base.'-'.strtoupper(Str::slug($size)),
                'size' => $size,
                'color' => $color,
                'stock' => 15,
                'is_active' => true,
            ]);
        }
    }

    private function seedCoupons(): void
    {
        Coupon::create([
            'code' => 'WELCOME10',
            'type' => 'percent',
            'value' => 10,
            'is_active' => true,
        ]);

        // Backs the "free shipping on orders over $150" perk.
        Coupon::create([
            'code' => 'FREESHIP150',
            'type' => 'fixed',
            'value' => 0,
            'min_subtotal' => 150,
            'free_shipping' => true,
            'is_active' => true,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ImageStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Catalogue CRUD. Sizes and colours are edited inline as a repeater rather
 * than on their own screen — a garment and its size run are one thought.
 */
class ProductController extends Controller
{
    public function __construct(private readonly ImageStore $images) {}

    public function index(Request $request)
    {
        $products = Product::query()
            ->with(['category', 'images'])
            ->withSum('variants as stock_total', 'stock')
            ->when($request->string('q')->trim()->value(), fn ($q, $term) => $q->search($term))
            ->when($request->integer('category'), fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->input('status') === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->input('status') === 'draft', fn ($q) => $q->where('is_active', false))
            ->when($request->input('status') === 'featured', fn ($q) => $q->where('is_featured', true))
            ->when($request->input('status') === 'sale', fn ($q) => $q->onSale())
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', [
            'active' => 'products',
            'products' => $products,
            'categories' => Category::ordered()->get(),
        ]);
    }

    public function create()
    {
        return view('admin.products.form', [
            'active' => 'products',
            'product' => new Product(['is_active' => true, 'rating' => 5]),
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $product = DB::transaction(function () use ($data, $request) {
            $product = Product::create($data);
            $this->syncVariants($product, $request);
            $this->attachUploads($product, $request);

            return $product;
        });

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('status', $product->name.' created.');
    }

    public function edit(Product $product)
    {
        $product->load(['variants' => fn ($q) => $q->orderBy('id'), 'images' => fn ($q) => $q->orderBy('position')]);

        return view('admin.products.form', [
            'active' => 'products',
            'product' => $product,
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, $product);

        DB::transaction(function () use ($product, $data, $request) {
            $product->update($data);
            $this->syncVariants($product, $request);
            $this->attachUploads($product, $request);
        });

        return back()->with('status', $product->name.' saved.');
    }

    public function destroy(Product $product)
    {
        $name = $product->name;

        DB::transaction(function () use ($product) {
            // Order items snapshot their own product name and price, so sales
            // history survives this; only the catalogue row and its files go.
            foreach ($product->images as $image) {
                $this->images->forget($image->disk_path);
            }

            $product->images()->delete();
            $product->variants()->delete();
            $product->delete();
        });

        return redirect()
            ->route('admin.products.index')
            ->with('status', $name.' deleted.');
    }

    /**
     * Flip `is_active` or `is_featured` straight from the list.
     */
    public function toggle(Request $request, Product $product)
    {
        $field = $request->validate([
            'field' => ['required', Rule::in(['is_active', 'is_featured'])],
        ])['field'];

        $product->update([$field => ! $product->{$field}]);

        return back()->with('status', $product->name.' updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('products', 'slug')->ignore($product)],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0', 'max:999999', 'gt:price'],
            'badge' => ['nullable', 'string', 'max:32'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'sale_ends_at' => ['nullable', 'date'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => array_merge(['nullable'], ImageStore::RULES),
        ], [
            'compare_at_price.gt' => 'The "was" price must be higher than the price, or the piece is not on sale.',
        ]);

        unset($data['photos']);

        $data['slug'] = ($data['slug'] ?? null) ?: $this->uniqueSlug($data['name'], $product);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');
        $data['badge'] = ($data['badge'] ?? null) ? strtoupper($data['badge']) : null;

        return $data;
    }

    private function uniqueSlug(string $name, ?Product $product): string
    {
        $base = Str::slug($name) ?: 'piece';
        $slug = $base;
        $n = 2;

        while (Product::where('slug', $slug)->when($product, fn ($q) => $q->whereKeyNot($product->id))->exists()) {
            $slug = $base.'-'.$n++;
        }

        return $slug;
    }

    /**
     * Reconcile the inline size/colour repeater against what is stored. Rows
     * the form no longer carries are deleted; rows with an id are updated in
     * place so their order history keeps pointing at the same variant.
     */
    private function syncVariants(Product $product, Request $request): void
    {
        $rows = collect($request->input('variants', []))
            ->filter(fn ($row) => filled($row['size'] ?? null) || filled($row['color'] ?? null));

        $keptIds = [];

        foreach ($rows as $row) {
            $attributes = [
                'sku' => filled($row['sku'] ?? null) ? $row['sku'] : $this->generateSku($product, $row),
                'size' => filled($row['size'] ?? null) ? $row['size'] : null,
                'color' => filled($row['color'] ?? null) ? $row['color'] : null,
                'price_override' => filled($row['price_override'] ?? null) ? (float) $row['price_override'] : null,
                'stock' => max(0, (int) ($row['stock'] ?? 0)),
                'is_active' => (bool) ($row['is_active'] ?? false),
            ];

            $variant = filled($row['id'] ?? null)
                ? $product->variants()->find($row['id'])
                : null;

            if ($variant) {
                $variant->update($attributes);
            } else {
                $variant = $product->variants()->create($attributes);
            }

            $keptIds[] = $variant->id;
        }

        $product->variants()->whereNotIn('id', $keptIds ?: [0])->delete();
    }

    /**
     * A SKU for a variant whose row was left blank. `sku` is NOT NULL and
     * unique, so we derive a stable-ish code from the slug, size and colour and
     * guarantee uniqueness with a short suffix.
     *
     * @param  array<string, mixed>  $row
     */
    private function generateSku(Product $product, array $row): string
    {
        $base = strtoupper(collect([
            Str::slug($product->slug ?: $product->name),
            $row['size'] ?? null,
            $row['color'] ?? null,
        ])->filter()->map(fn ($p) => Str::slug($p))->implode('-')) ?: 'TC';

        do {
            $sku = $base.'-'.strtoupper(Str::random(4));
        } while (ProductVariant::where('sku', $sku)->exists());

        return $sku;
    }

    /**
     * Store any newly uploaded photographs. The first image on a product with
     * no imagery becomes the primary one, so a new piece is never faceless.
     */
    private function attachUploads(Product $product, Request $request): void
    {
        $files = array_filter($request->file('photos', []));

        if ($files === []) {
            return;
        }

        $position = (int) $product->images()->max('position');
        $hasPrimary = $product->images()->where('is_primary', true)->exists();

        foreach ($files as $file) {
            $stored = $this->images->store($file, 'products/'.$product->id);

            $product->images()->create([
                'url' => $stored['url'],
                'disk_path' => $stored['path'],
                'is_primary' => ! $hasPrimary,
                'position' => ++$position,
            ]);

            $hasPrimary = true;
        }
    }

    /**
     * Categories as an indented list, so the tree reads as a tree in a select.
     * Products live on leaves, but a parent is still selectable — the shop
     * widens a parent to its children, so nothing breaks either way.
     *
     * @return array<int, string>
     */
    private function categoryOptions(): array
    {
        $options = [];

        foreach (Category::with('children.children')->roots()->ordered()->get() as $root) {
            $this->flattenOptions($root, $options);
        }

        return $options;
    }

    /**
     * @param  array<int, string>  $options
     */
    private function flattenOptions(Category $category, array &$options, int $depth = 0): void
    {
        $options[$category->id] = str_repeat('— ', $depth).$category->name;

        foreach ($category->children as $child) {
            $this->flattenOptions($child, $options, $depth + 1);
        }
    }
}

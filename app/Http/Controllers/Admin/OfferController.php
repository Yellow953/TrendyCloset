<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OfferType;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Automatic, cart-level promotions — no code to type, unlike a Coupon. One
 * table covers three shapes (see {@see OfferType}); `validated()` normalises
 * away whichever columns the chosen type does not use, so a form re-submitted
 * after switching type never leaves stale data behind.
 */
class OfferController extends Controller
{
    public function index(Request $request)
    {
        $offers = Offer::query()
            ->with(['category', 'products'])
            ->when($request->string('q')->trim()->value(), fn ($q, $term) => $q->where('name', 'like', '%'.$term.'%'))
            ->when($request->input('filter') === 'active', fn ($q) => $q->live())
            ->when($request->input('filter') === 'expired', fn ($q) => $q->whereNotNull('expires_at')->where('expires_at', '<', now()))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.offers.index', [
            'active' => 'offers',
            'offers' => $offers,
            'categoryOptions' => $this->categoryOptions(),
            'productOptions' => $this->productOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $offer = Offer::create($this->validated($request));
        $offer->products()->sync($this->productIds($request));

        return redirect()
            ->route('admin.offers.index')
            ->with('status', $offer->name.' created.');
    }

    public function update(Request $request, Offer $offer)
    {
        $offer->update($this->validated($request, $offer));
        $offer->products()->sync($this->productIds($request));

        return back()->with('status', $offer->name.' saved.');
    }

    public function destroy(Offer $offer)
    {
        $name = $offer->name;
        $offer->delete();

        return redirect()
            ->route('admin.offers.index')
            ->with('status', $name.' deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Offer $offer = null): array
    {
        $type = $request->enum('type', OfferType::class) ?? OfferType::Spend;
        $scope = $request->string('scope')->value() ?: 'category';
        $usesCategory = in_array($type, [OfferType::CategoryPercent, OfferType::Bogo], true) && $scope === 'category';

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'headline' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::enum(OfferType::class)],
            'discount_type' => ['nullable', Rule::in(['percent', 'fixed'])],
            'value' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'category_id' => [
                Rule::requiredIf($type === OfferType::CategoryPercent && $scope === 'category'),
                'nullable', 'exists:categories,id',
            ],
            'products' => [Rule::requiredIf($scope === 'products'), 'nullable', 'array'],
            'products.*' => ['integer', 'exists:products,id'],
            'min_subtotal' => ['nullable', 'numeric', 'min:0'],
            'buy_qty' => ['nullable', 'integer', 'min:1'],
            'get_qty' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],
            'free_shipping' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'products.required' => 'Choose at least one product.',
        ]);

        unset($data['products']); // synced onto the pivot separately, not a column

        $data['free_shipping'] = $request->boolean('free_shipping');
        $data['is_active'] = $request->boolean('is_active');
        $data['category_id'] = $usesCategory ? ($data['category_id'] ?? null) : null;

        // Only the columns the chosen type actually reads are kept — a form
        // re-saved after switching type never leaves a stale category, spend
        // threshold or BOGO count sitting behind the scenes.
        return match ($type) {
            OfferType::Spend => [
                ...$data,
                'discount_type' => $data['discount_type'] ?? 'percent',
                'value' => $data['value'] ?? 0,
                'category_id' => null,
                'buy_qty' => null,
                'get_qty' => null,
            ],
            OfferType::CategoryPercent => [
                ...$data,
                'discount_type' => null,
                'value' => min((float) ($data['value'] ?? 0), 100),
                'min_subtotal' => null,
                'free_shipping' => false,
                'buy_qty' => null,
                'get_qty' => null,
            ],
            OfferType::Bogo => [
                ...$data,
                'discount_type' => null,
                'value' => 0,
                'min_subtotal' => null,
                'free_shipping' => false,
                'buy_qty' => $data['buy_qty'] ?? 1,
                'get_qty' => $data['get_qty'] ?? 1,
            ],
        };
    }

    /**
     * The hand-picked product ids to sync onto the pivot — only meaningful
     * for the two product-facing types with "Specific products" chosen; every
     * other combination clears the pivot, mirroring how validated() clears
     * category_id for the columns a type doesn't read.
     *
     * @return array<int, int>
     */
    private function productIds(Request $request): array
    {
        $type = $request->enum('type', OfferType::class) ?? OfferType::Spend;
        $scope = $request->string('scope')->value() ?: 'category';

        if ($scope !== 'products' || ! in_array($type, [OfferType::CategoryPercent, OfferType::Bogo], true)) {
            return [];
        }

        return array_map('intval', $request->input('products', []));
    }

    /**
     * Categories as an indented list, so the tree reads as a tree in a select.
     * A null-keyed "storewide" option covers the BOGO types that scope to
     * every product rather than one category.
     *
     * @return array<int|string, string>
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

    /**
     * @return array<int, string>
     */
    private function productOptions(): array
    {
        return Product::query()->orderBy('name')->pluck('name', 'id')->all();
    }
}

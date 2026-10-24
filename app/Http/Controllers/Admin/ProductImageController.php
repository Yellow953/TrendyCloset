<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * The photographs on one product. Uploading happens on the product form
 * itself; these are the per-image actions that need their own verb.
 */
class ProductImageController extends Controller
{
    public function __construct(private readonly ImageStore $images) {}

    /**
     * Make one image the one the cards and the PDP lead with.
     */
    public function primary(Product $product, ProductImage $image)
    {
        abort_unless($image->product_id === $product->id, 404);

        $order = $product->images()->orderBy('position')->pluck('id')
            ->reject(fn ($id) => $id === $image->id)->prepend($image->id);

        $this->applyOrder($product, $order->all());

        return back()->with('status', 'Primary image updated.');
    }

    public function destroy(Product $product, ProductImage $image)
    {
        abort_unless($image->product_id === $product->id, 404);

        DB::transaction(function () use ($product, $image) {
            $wasPrimary = $image->is_primary;

            $this->images->forget($image->disk_path);
            $image->delete();

            // Never leave a product with photographs but no primary one.
            if ($wasPrimary) {
                $product->images()->orderBy('position')->first()?->update(['is_primary' => true]);
            }
        });

        return back()->with('status', 'Image removed.');
    }

    /**
     * Persist a new running order for the gallery. The first image leads, so
     * it is also the primary one — the row in the form is the row a shopper sees.
     */
    public function reorder(Request $request, Product $product)
    {
        $order = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer'],
        ])['order'];

        $this->applyOrder($product, array_values($order));

        return $request->expectsJson()
            ? response()->json(['status' => 'ok'])
            : back()->with('status', 'Gallery reordered.');
    }

    private function applyOrder(Product $product, array $order): void
    {
        $ids = $product->images()->whereKey($order)->pluck('id')->all();
        $order = array_values(array_filter($order, fn ($id) => in_array($id, $ids)));

        if ($order === []) {
            return;
        }

        DB::transaction(function () use ($product, $order) {
            foreach ($order as $position => $id) {
                $product->images()->whereKey($id)->update([
                    'position' => $position + 1,
                    'is_primary' => $position === 0,
                ]);
            }
        });
    }
}

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

        DB::transaction(function () use ($product, $image) {
            $product->images()->update(['is_primary' => false]);
            $image->update(['is_primary' => true]);
        });

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
     * Persist a new running order for the gallery.
     */
    public function reorder(Request $request, Product $product)
    {
        $order = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer'],
        ])['order'];

        foreach (array_values($order) as $position => $id) {
            $product->images()->whereKey($id)->update(['position' => $position + 1]);
        }

        return back()->with('status', 'Gallery reordered.');
    }
}

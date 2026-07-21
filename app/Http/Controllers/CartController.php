<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Services\ProductAnalytics;
use App\Support\Cart;
use App\Support\Seo;
use Illuminate\Http\Request;

/**
 * The bag. State lives in the session (see {@see Cart}) — nothing here writes
 * an order; checkout is still presentational.
 */
class CartController extends Controller
{
    public function __construct(
        private readonly Cart $cart,
        private readonly Seo $seo,
    ) {}

    public function index()
    {
        // Session state, unique per shopper: never index the bag or checkout.
        $this->seo->page('Your Bag')->noindex();

        return view('store.cart', [
            'lines' => $this->cart->lines(),
            'summary' => $this->cart->summary(),
            'freeShippingRemainder' => $this->cart->freeShippingRemainder(),
            'active' => null,
        ]);
    }

    /**
     * Add to bag. The chosen variant carries the size/colour, so this is also
     * where the add_to_cart analytics event is recorded.
     */
    public function store(Request $request, ProductAnalytics $analytics)
    {
        $data = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $variant = ProductVariant::with('product')->findOrFail($data['variant_id']);

        if (! $variant->is_active || ! $variant->in_stock || ! $variant->product?->is_active) {
            return back()->withErrors(['variant_id' => 'That size just sold out.']);
        }

        $this->cart->add($variant, (int) ($data['quantity'] ?? 1));
        $analytics->recordAddToCart($variant->product);

        // "Buy now" is the same add, but it takes you straight to checkout.
        if ($request->input('action') === 'buy') {
            return redirect()->route('checkout');
        }

        return back()->with('status', $variant->product->name.' added to your bag.');
    }

    public function update(Request $request, ProductVariant $variant)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:20'],
        ]);

        $this->cart->setQuantity($variant, (int) $data['quantity']);

        return back();
    }

    public function destroy(ProductVariant $variant)
    {
        $this->cart->remove($variant);

        return back()->with('status', 'Item removed from your bag.');
    }

    public function applyCoupon(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:64'],
        ]);

        if (! $this->cart->applyCoupon($data['code'])) {
            return back()->withErrors(['code' => 'That code is not valid for this bag.']);
        }

        return back()->with('status', 'Discount applied.');
    }

    public function removeCoupon()
    {
        $this->cart->removeCoupon();

        return back();
    }

    public function checkout()
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart');
        }

        $this->seo->page('Checkout')->noindex();

        return view('store.checkout', [
            'lines' => $this->cart->lines(),
            'summary' => $this->cart->summary(),
            'active' => null,
        ]);
    }
}

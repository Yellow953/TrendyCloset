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
     * The slide-over bag's contents, as an HTML fragment. Fetched by app.js
     * when the drawer opens and re-fetched after every change — rendering the
     * bag server-side keeps one source of truth for prices and stock.
     */
    public function drawer()
    {
        return view('partials.drawer-bag', [
            'lines' => $this->cart->lines(),
            'summary' => $this->cart->summary(),
            'count' => $this->cart->count(),
            'freeShippingRemainder' => $this->cart->freeShippingRemainder(),
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
            $message = 'That size just sold out.';

            return $request->expectsJson()
                ? response()->json(['message' => $message], 422)
                : back()->withErrors(['variant_id' => $message]);
        }

        $this->cart->add($variant, (int) ($data['quantity'] ?? 1));
        $analytics->recordAddToCart($variant->product);

        $status = $variant->product->name.' added to your bag.';

        // The card and PDP buttons post this over fetch (see initAsyncForms in
        // app.js) so adding never costs the shopper their scroll position.
        if ($request->expectsJson()) {
            return response()->json([
                'status' => $status,
                'bagCount' => $this->cart->count(),
            ]);
        }

        // "Buy now" is the same add, but it takes you straight to checkout.
        if ($request->input('action') === 'buy') {
            return redirect()->route('checkout');
        }

        return back()->with('status', $status);
    }

    public function update(Request $request, ProductVariant $variant)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:20'],
        ]);

        $this->cart->setQuantity($variant, (int) $data['quantity']);

        return $request->expectsJson()
            ? response()->json(['bagCount' => $this->cart->count()])
            : back();
    }

    public function destroy(Request $request, ProductVariant $variant)
    {
        $this->cart->remove($variant);

        $status = 'Item removed from your bag.';

        return $request->expectsJson()
            ? response()->json(['status' => $status, 'bagCount' => $this->cart->count()])
            : back()->with('status', $status);
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

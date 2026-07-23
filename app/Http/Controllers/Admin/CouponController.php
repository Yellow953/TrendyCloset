<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Discount codes. Admin-only — the `admin` middleware gates the whole group.
 * Codes are stored uppercase; the bag matches case-insensitively.
 */
class CouponController extends Controller
{
    public function index(Request $request)
    {
        $coupons = Coupon::query()
            ->withCount('orders')
            ->when($request->string('q')->trim()->value(), fn ($q, $term) => $q->where('code', 'like', '%'.$term.'%'))
            ->when($request->input('filter') === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->input('filter') === 'expired', fn ($q) => $q->whereNotNull('expires_at')->where('expires_at', '<', now()))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.coupons.index', [
            'active' => 'coupons',
            'coupons' => $coupons,
        ]);
    }


    public function store(Request $request)
    {
        $coupon = Coupon::create($this->validated($request));

        return redirect()
            ->route('admin.coupons.index')
            ->with('status', $coupon->code.' created.');
    }


    public function update(Request $request, Coupon $coupon)
    {
        $coupon->update($this->validated($request, $coupon));

        return back()->with('status', $coupon->code.' saved.');
    }

    public function destroy(Coupon $coupon)
    {
        $code = $coupon->code;
        $coupon->delete();

        return redirect()
            ->route('admin.coupons.index')
            ->with('status', $code.' deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Coupon $coupon = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:64', 'alpha_dash', Rule::unique('coupons', 'code')->ignore($coupon)],
            'type' => ['required', Rule::in(['percent', 'fixed'])],
            'value' => ['required', 'numeric', 'min:0', Rule::when($request->input('type') === 'percent', ['max:100'], ['max:99999'])],
            'min_subtotal' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],
            'free_shipping' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['code'] = strtoupper($data['code']);
        $data['free_shipping'] = $request->boolean('free_shipping');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}

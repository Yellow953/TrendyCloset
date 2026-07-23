<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The CRM side. Customers never sign in (see the identity split in CLAUDE.md) —
 * these are records matched on email at checkout, so the shop can see repeat
 * business and keep notes against a name.
 */
class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::query()
            ->withCount('orders')
            ->withSum(['orders as lifetime_value' => fn ($q) => $q->revenue()], 'grand_total')
            ->withMax('orders as last_order_at', 'created_at')
            ->when($request->string('q')->trim()->value(), function ($q, $term) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';
                $q->where(fn ($w) => $w->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like));
            })
            ->when($request->input('filter') === 'subscribed', fn ($q) => $q->where('marketing_opt_in', true))
            ->when($request->input('filter') === 'repeat', fn ($q) => $q->has('orders', '>=', 2))
            ->when($request->input('filter') === 'never', fn ($q) => $q->doesntHave('orders'))
            ->orderByDesc($request->input('sort') === 'value' ? 'lifetime_value' : 'id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.customers.index', [
            'active' => 'customers',
            'customers' => $customers,
        ]);
    }

    public function show(Customer $customer)
    {
        $customer->load(['orders' => fn ($q) => $q->withCount('items')->latest()]);

        return view('admin.customers.show', [
            'active' => 'customers',
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customer)],
            'phone' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'marketing_opt_in' => ['nullable', 'boolean'],
        ]);

        $data['email'] = mb_strtolower(trim($data['email']));
        $data['marketing_opt_in'] = $request->boolean('marketing_opt_in');

        $customer->update($data);

        return back()->with('status', $customer->name.' saved.');
    }

    public function destroy(Customer $customer)
    {
        // `orders.customer_id` is nullOnDelete on purpose: losing a CRM record
        // must never destroy the sales history attached to it.
        $name = $customer->name;
        $customer->delete();

        return redirect()
            ->route('admin.customers.index')
            ->with('status', $name.' deleted. Their orders were kept.');
    }
}

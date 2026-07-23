<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Checkout;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Orders are a snapshot of a sale as it was placed — the product name, size,
 * colour and price on each line are copied, never joined. Nothing here edits
 * those. What the back office does is move an order's status along and keep
 * notes against it.
 */
class OrderController extends Controller
{
    public function __construct(private readonly Checkout $checkout) {}

    public function index(Request $request)
    {
        $orders = Order::query()
            ->with('customer')
            ->withCount('items')
            ->when($request->string('q')->trim()->value(), fn ($q, $term) => $q->search($term))
            ->when($request->input('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->input('range') === 'today', fn ($q) => $q->whereDate('created_at', today()))
            ->when($request->input('range') === 'week', fn ($q) => $q->where('created_at', '>=', now()->subWeek()))
            ->when($request->input('range') === 'month', fn ($q) => $q->where('created_at', '>=', now()->subMonth()))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', [
            'active' => 'orders',
            'orders' => $orders,
            'statuses' => OrderStatus::cases(),
            'revenue' => (float) Order::revenue()
                ->when($request->string('q')->trim()->value(), fn ($q, $term) => $q->search($term))
                ->when($request->input('status'), fn ($q, $status) => $q->where('status', $status))
                ->sum('grand_total'),
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['items.variant.product', 'customer', 'coupon']);

        return view('admin.orders.show', [
            'active' => 'orders',
            'order' => $order,
            'statuses' => OrderStatus::cases(),
        ]);
    }

    /**
     * Move the order along. Crossing the line between "live" and
     * "cancelled/refunded" moves stock with it, so the rail always reflects
     * what is actually committed.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $status = OrderStatus::from($request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ])['status']);

        $was = $order->status;

        if ($status === $was) {
            return back();
        }

        if ($was->holdsStock() && ! $status->holdsStock()) {
            $this->checkout->restock($order);
        } elseif (! $was->holdsStock() && $status->holdsStock()) {
            $this->checkout->destock($order);
        }

        $order->update(['status' => $status]);

        return back()->with('status', $order->order_number.' is now '.$status->label().'.');
    }

    public function updateNotes(Request $request, Order $order)
    {
        $order->update($request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
        ]));

        return back()->with('status', 'Notes saved.');
    }
}

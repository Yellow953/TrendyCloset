<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'coupon_id',
        'order_number',
        'status',
        'email',
        'ship_name',
        'ship_street',
        'ship_building',
        'ship_floor',
        'ship_details',
        'ship_city',
        'ship_region',
        'ship_postcode',
        'ship_country',
        'ship_phone',
        'subtotal',
        'discount_total',
        'shipping_total',
        'grand_total',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'shipping_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Customer, Order>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<Coupon, Order>
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * @return HasMany<OrderItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Total number of garments on the order (not the number of lines).
     */
    public function getQuantityAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }

    /**
     * The shipping address as the lines you would write on a label. Building
     * and floor share a line, the way an address is actually read out.
     *
     * @return array<int, string>
     */
    public function addressLines(): array
    {
        $building = trim(implode(', ', array_filter([
            $this->ship_building,
            $this->ship_floor ? 'Floor '.$this->ship_floor : null,
        ])));

        return array_values(array_filter([
            $this->ship_name,
            $this->ship_street,
            $building,
            $this->ship_details,
            trim(implode(' ', array_filter([$this->ship_city, $this->ship_postcode]))),
            $this->ship_region,
            $this->ship_country,
        ]));
    }

    /**
     * The next order number, e.g. "TC-20260722-0007". Human-readable and
     * sortable, which matters more here than being unguessable — the
     * confirmation page is gated on the session, not on the number.
     */
    public static function nextNumber(): string
    {
        $prefix = 'TC-'.now()->format('Ymd').'-';

        $last = static::where('order_number', 'like', $prefix.'%')
            ->orderByDesc('order_number')
            ->value('order_number');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Orders that count towards revenue — cancelled and refunded ones stay in
     * the table but must never be totalled.
     *
     * @param  Builder<Order>  $query
     */
    public function scopeRevenue(Builder $query): void
    {
        $query->whereIn('status', OrderStatus::revenueValues());
    }

    /**
     * Orders still waiting on someone in the back office.
     *
     * @param  Builder<Order>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->whereIn('status', array_map(
            fn (OrderStatus $s) => $s->value,
            array_filter(OrderStatus::cases(), fn (OrderStatus $s) => $s->isOpen()),
        ));
    }

    /**
     * Back-office search: order number, phone, email or the name on the parcel
     * — whichever the person on the phone reads out.
     *
     * @param  Builder<Order>  $query
     */
    public function scopeSearch(Builder $query, string $term): void
    {
        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';

        $query->where(function (Builder $q) use ($like) {
            $q->where('order_number', 'like', $like)
                ->orWhere('ship_phone', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('ship_name', 'like', $like);
        });
    }
}

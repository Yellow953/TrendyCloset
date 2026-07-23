<?php

namespace App\Enums;

/**
 * The lifecycle of an order. There is no payment gateway yet, so an order is
 * born `Pending` and a human moves it along from the back office.
 */
enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Paid => 'Paid',
            self::Processing => 'Processing',
            self::Shipped => 'Shipped',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
            self::Refunded => 'Refunded',
        };
    }

    /**
     * Tailwind classes for the status pill, so one status looks the same on the
     * dashboard, the order list and the order itself.
     */
    public function classes(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-50 text-amber-600 border-amber-200',
            self::Paid => 'bg-slate-100 text-slate-700 border-slate-300',
            self::Processing => 'bg-slate-100 text-slate-600 border-slate-200',
            self::Shipped => 'bg-slate-800 text-white border-slate-800',
            self::Completed => 'bg-emerald-50 text-emerald-600 border-emerald-200',
            self::Cancelled => 'bg-slate-100 text-slate-400 border-slate-200',
            self::Refunded => 'bg-rose-50 text-rose-600 border-rose-200',
        };
    }

    /**
     * Whether the order still needs someone to do something about it — what the
     * dashboard's "needs attention" count is made of.
     */
    public function isOpen(): bool
    {
        return in_array($this, [self::Pending, self::Paid, self::Processing], true);
    }

    /**
     * Whether the order counts as revenue. Cancelled and refunded orders stay
     * in the table (sales history is never destroyed) but must not be totalled.
     */
    public function countsAsRevenue(): bool
    {
        return ! in_array($this, [self::Cancelled, self::Refunded], true);
    }

    /**
     * Statuses whose stock has been taken out of the catalogue. Moving an order
     * out of one of these puts the garments back on the rail.
     */
    public function holdsStock(): bool
    {
        return $this->countsAsRevenue();
    }

    /**
     * @return array<int, self>
     */
    public static function revenueCases(): array
    {
        return array_values(array_filter(self::cases(), fn (self $s) => $s->countsAsRevenue()));
    }

    /**
     * @return array<int, string>
     */
    public static function revenueValues(): array
    {
        return array_map(fn (self $s) => $s->value, self::revenueCases());
    }
}

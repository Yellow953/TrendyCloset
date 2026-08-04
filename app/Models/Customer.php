<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A customer of the store. Deliberately NOT authenticatable: customers check
 * out as guests and never sign in. This is a CRM record, matched on phone at
 * checkout (email is optional), so the back office can see repeat business.
 */
class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'marketing_opt_in',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'marketing_opt_in' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Order>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * A phone number in one canonical form, so matching is exact: "76 158 735",
     * "076158735" and "+961 76 158 735" all become +96176158735. A number that
     * already carries another country's code is left alone.
     */
    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        // An explicit "+" means the country code is already in front of the
        // number — checkout puts it there from the code select, so a short
        // foreign number must not be read as a local one.
        if (str_starts_with(ltrim($phone), '+')) {
            return '+'.$digits;
        }

        $code = (string) config('store.contact.country_code', '961');

        $digits = preg_replace('/^00/', '', $digits) ?? $digits;

        if (str_starts_with($digits, $code)) {
            return '+'.$digits;
        }

        $local = ltrim($digits, '0');

        return mb_strlen($local) <= 8 ? '+'.$code.$local : '+'.$digits;
    }

    /**
     * Find the customer behind a checkout, or create one. Email is filled in
     * when given but never matched on — plenty of orders arrive without one.
     */
    public static function forPhone(string $phone, array $attributes = []): self
    {
        $attributes['email'] = isset($attributes['email']) && $attributes['email'] !== null
            ? mb_strtolower(trim($attributes['email']))
            : null;

        return static::firstOrCreate(
            ['phone' => static::normalizePhone($phone)],
            $attributes,
        );
    }

    /**
     * What to call this record in a list.
     */
    public function label(): string
    {
        return $this->name ?: ($this->phone ?: ($this->email ?: 'Customer #'.$this->id));
    }

    /**
     * Total value of every order this customer has placed.
     */
    public function lifetimeValue(): float
    {
        return (float) $this->orders()->sum('grand_total');
    }
}

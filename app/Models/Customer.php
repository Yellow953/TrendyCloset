<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A customer of the store. Deliberately NOT authenticatable: customers check
 * out as guests and never sign in. This is a CRM record, matched on email at
 * checkout, so the back office can see repeat business and keep notes.
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
     * Find the customer for an email, or create one. Emails are normalised so
     * "Leila@Example.com " and "leila@example.com" are the same person.
     */
    public static function forEmail(string $email, array $attributes = []): self
    {
        return static::firstOrCreate(
            ['email' => mb_strtolower(trim($email))],
            $attributes,
        );
    }

    /**
     * Total value of every order this customer has placed.
     */
    public function lifetimeValue(): float
    {
        return (float) $this->orders()->sum('grand_total');
    }
}

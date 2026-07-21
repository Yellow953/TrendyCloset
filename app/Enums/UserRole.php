<?php

namespace App\Enums;

/**
 * Roles for internal (back-office) users. Customers are NOT users — they never
 * sign in and live in their own `customers` table.
 */
enum UserRole: string
{
    case Admin = 'admin';
    case Staff = 'staff';

    /**
     * Human label for the CRM UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Staff => 'Staff',
        };
    }

    /**
     * Whether the role may manage other users, coupons, and store settings.
     */
    public function managesStore(): bool
    {
        return $this === self::Admin;
    }
}

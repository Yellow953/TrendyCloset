<?php

namespace App\Enums;

/**
 * The crop a product's photographs are locked to on upload. Chosen once per
 * product (not per photo) so the card, thumbnail rail and PDP main image stay
 * one shape for that piece — see {@see \App\Services\ImageStore::crop()}.
 */
enum PhotoRatio: string
{
    case Square = '1:1';
    case Portrait = '4:5';

    public function label(): string
    {
        return match ($this) {
            self::Square => 'Square (1:1)',
            self::Portrait => 'Portrait (4:5)',
        };
    }

    /**
     * Width ÷ height, for {@see \App\Services\ImageStore::store()}.
     */
    public function ratio(): float
    {
        return match ($this) {
            self::Square => 1.0,
            self::Portrait => 0.8,
        };
    }

    /**
     * Tailwind class for the frame a photograph of this ratio sits in.
     */
    public function aspectClass(): string
    {
        return match ($this) {
            self::Square => 'aspect-square',
            self::Portrait => 'aspect-[4/5]',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->all();
    }
}

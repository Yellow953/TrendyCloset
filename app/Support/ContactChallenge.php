<?php

namespace App\Support;

/**
 * The contact form's own bot check: a sum asked in words, a hidden field no
 * person fills, and a minimum time between seeing the form and sending it.
 * State lives in the session, so nothing here needs JS or a third party.
 */
class ContactChallenge
{
    public const HONEYPOT = 'contact_me_by_fax';

    private const KEY = 'tc_contact_challenge';

    private const MIN_SECONDS = 3;

    private const WORDS = [1 => 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];

    public static function issue(): string
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);

        session([self::KEY => ['answer' => $a + $b, 'at' => time()]]);

        return 'What is '.self::WORDS[$a].' plus '.self::WORDS[$b].'?';
    }

    public static function looksAutomated(?string $honeypot): bool
    {
        $issued = session(self::KEY);

        return filled($honeypot)
            || ! is_array($issued)
            || time() - $issued['at'] < self::MIN_SECONDS;
    }

    public static function passes(?string $answer): bool
    {
        $answer = trim((string) $answer);

        return ctype_digit($answer) && (int) $answer === session(self::KEY.'.answer');
    }

    public static function clear(): void
    {
        session()->forget(self::KEY);
    }
}

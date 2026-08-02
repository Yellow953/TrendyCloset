<?php

namespace App\Support;

/**
 * Turns a referrer host into the name a shopkeeper would use. "Instagram" is
 * useful; "l.instagram.com" is noise, and there are four of those.
 *
 * Anything unrecognised keeps its host, so a new channel shows up as itself
 * rather than being swept into "Other" where nobody would notice it.
 */
class TrafficSource
{
    /**
     * Matched as substrings of the host, most specific first.
     *
     * @var array<string, array<int, string>>
     */
    private const CHANNELS = [
        'Google' => ['google.', 'googleusercontent', 'googleadservices'],
        'Instagram' => ['instagram.', 'ig.me', 'cdninstagram'],
        'Facebook' => ['facebook.', 'fb.com', 'fb.me', 'messenger.'],
        'TikTok' => ['tiktok.', 'bytedance'],
        'WhatsApp' => ['whatsapp.', 'wa.me'],
        'Pinterest' => ['pinterest.', 'pin.it'],
        'X' => ['twitter.', 't.co', 'x.com'],
        'YouTube' => ['youtube.', 'youtu.be'],
        'Snapchat' => ['snapchat.'],
        'Bing' => ['bing.'],
        'DuckDuckGo' => ['duckduckgo.'],
        'Email' => ['mail.google', 'outlook.', 'mail.yahoo'],
    ];

    /**
     * A referrer host as a channel name. No referrer means the address was
     * typed, bookmarked, or came from an app that strips it — all of which the
     * trade calls "Direct".
     */
    public static function label(?string $host): string
    {
        if (blank($host)) {
            return 'Direct';
        }

        $host = mb_strtolower($host);

        foreach (self::CHANNELS as $label => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($host, $needle)) {
                    return $label;
                }
            }
        }

        return str_starts_with($host, 'www.') ? mb_substr($host, 4) : $host;
    }

    /**
     * The host part of a referrer, or null when it is missing or is the shop
     * itself — someone moving between our own pages is not a traffic source.
     */
    public static function host(?string $referrer): ?string
    {
        if (blank($referrer)) {
            return null;
        }

        $host = parse_url($referrer, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return null;
        }

        $own = parse_url((string) config('app.url'), PHP_URL_HOST);

        if (is_string($own) && $own !== '' && mb_strtolower($host) === mb_strtolower($own)) {
            return null;
        }

        return mb_substr($host, 0, 128);
    }
}

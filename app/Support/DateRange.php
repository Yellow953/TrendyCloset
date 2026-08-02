<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * The window a report covers, resolved from the query string once and then
 * passed around. Every figure on a reporting page has to agree about where the
 * period starts, where it ends and what it is being compared against — so that
 * lives here rather than being recomputed per card.
 *
 * A range is either a preset ("last 30 days") or a pair of dates the person
 * typed. Both end up as the same two Carbons, so nothing downstream cares which
 * it was.
 */
class DateRange
{
    public const DEFAULT = '30d';

    /** Presets, in the order the filter offers them. */
    public const PRESETS = [
        '7d' => 'Last 7 days',
        '30d' => 'Last 30 days',
        '90d' => 'Last 90 days',
        'year' => 'Last 12 months',
        'all' => 'All time',
        'custom' => 'Custom range…',
    ];

    /**
     * @param  ?Carbon  $start  Null means "all time" — no lower bound at all.
     */
    public function __construct(
        private readonly ?Carbon $start,
        private readonly Carbon $end,
        private readonly string $preset,
    ) {}

    /**
     * Read `range`, `from` and `to` off the request. Anything unparseable falls
     * back to the default rather than erroring: a report is not worth a 500,
     * and a mistyped URL should still show something.
     */
    public static function fromRequest(Request $request): self
    {
        $preset = (string) $request->input('range', self::DEFAULT);

        if (! array_key_exists($preset, self::PRESETS)) {
            $preset = self::DEFAULT;
        }

        if ($preset === 'custom') {
            return self::custom($request->input('from'), $request->input('to'));
        }

        return self::fromPreset($preset);
    }

    public static function fromPreset(string $preset): self
    {
        $end = now()->endOfDay();

        $start = match ($preset) {
            '7d' => now()->subDays(6)->startOfDay(),
            '90d' => now()->subDays(89)->startOfDay(),
            'year' => now()->subMonths(12)->startOfDay(),
            'all' => null,
            default => now()->subDays(29)->startOfDay(),
        };

        return new self($start, $end, $preset);
    }

    /**
     * An arbitrary trailing window, for the fixed charts that are not driven by
     * a filter (the dashboard's fortnight).
     */
    public static function lastDays(int $days): self
    {
        return new self(
            now()->subDays($days - 1)->startOfDay(),
            now()->endOfDay(),
            'custom',
        );
    }

    /**
     * A typed window. Dates arrive from two <input type="date"> fields, so they
     * can be blank, reversed, or in the future — take all three in stride.
     */
    public static function custom(mixed $from, mixed $to): self
    {
        $start = self::parse($from);
        $end = self::parse($to);

        if (! $start && ! $end) {
            return self::fromPreset(self::DEFAULT);
        }

        $start ??= $end->copy()->subDays(29);
        $end ??= now();

        // Someone who picks the dates the wrong way round means the period
        // between them, not an empty one.
        if ($start->greaterThan($end)) {
            [$start, $end] = [$end, $start];
        }

        return new self(
            $start->startOfDay(),
            $end->min(now())->endOfDay(),
            'custom',
        );
    }

    private static function parse(mixed $value): ?Carbon
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable) {
            return null;
        }
    }

    public function start(): ?Carbon
    {
        return $this->start?->copy();
    }

    public function end(): Carbon
    {
        return $this->end->copy();
    }

    public function preset(): string
    {
        return $this->preset;
    }

    public function isAllTime(): bool
    {
        return $this->start === null;
    }

    public function label(): string
    {
        if ($this->isAllTime()) {
            return 'All time';
        }

        if ($this->preset !== 'custom') {
            return self::PRESETS[$this->preset];
        }

        return $this->start->format('j M Y').' – '.$this->end->format('j M Y');
    }

    /** The window's length in whole days, counting both ends. */
    public function days(): ?int
    {
        return $this->isAllTime()
            ? null
            : (int) $this->start->diffInDays($this->end) + 1;
    }

    /**
     * The window of the same length immediately before this one, which is what
     * every "up 12%" on the page is measured against. All time has nothing to
     * compare with, so it returns null and the deltas simply do not render.
     */
    public function previous(): ?self
    {
        if ($this->isAllTime()) {
            return null;
        }

        $end = $this->start->copy()->subSecond();

        return new self(
            $end->copy()->subDays($this->days() - 1)->startOfDay(),
            $end,
            $this->preset,
        );
    }

    /**
     * Constrain a query to the window. Deliberately a no-op for all time —
     * callers should not have to branch on it.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query, string $column = 'created_at'): Builder
    {
        if ($this->isAllTime()) {
            return $query;
        }

        return $query->whereBetween($column, [$this->start, $this->end]);
    }

    /**
     * How wide a chart bucket should be. A year of daily bars is 365 slivers
     * nobody can read, so the axis coarsens as the window grows — and a single
     * day would be one bar, so it goes the other way and splits into hours.
     *
     * @return 'hour'|'day'|'week'|'month'
     */
    public function granularity(): string
    {
        $days = $this->days();

        return match (true) {
            $days === null, $days > 180 => 'month',
            $days > 31 => 'week',
            $days <= 1 => 'hour',
            default => 'day',
        };
    }

    /**
     * The query-string form, for links that need to keep the current window
     * (sorting a table, jumping to a filtered order list).
     *
     * @return array<string, string>
     */
    public function toQuery(): array
    {
        if ($this->preset !== 'custom') {
            return ['range' => $this->preset];
        }

        return [
            'range' => 'custom',
            'from' => $this->start->toDateString(),
            'to' => $this->end->toDateString(),
        ];
    }
}

<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The two things every reporting card needs: how a figure moved, and a series
 * with a slot for every bucket in the window — including the quiet ones. Both
 * used to live privately on the dashboard; a second reporting screen made one
 * shared implementation the honest answer.
 */
class Stats
{
    /**
     * Percentage change, or null when there is no baseline to compare against
     * (showing "+100%" against zero would be theatre, not information).
     */
    public static function delta(float $now, float $prior): ?float
    {
        if ($prior <= 0) {
            return null;
        }

        return round((($now - $prior) / $prior) * 100, 1);
    }

    /**
     * Share of a total as a percentage, guarding the empty case so a card with
     * nothing in it renders a row of zeroes rather than dividing by it.
     */
    public static function share(float $part, float $total): float
    {
        return $total > 0 ? round(($part / $total) * 100, 1) : 0.0;
    }

    /**
     * A bucketed, zero-filled series over the range, ready for a bar chart.
     *
     * Rows are fetched and grouped in PHP rather than with a database `GROUP BY`
     * on a formatted date: the SQL for that is dialect-specific, and these are
     * back-office volumes. Pass `$sumColumn` to total a column (revenue), leave
     * it null to count rows (orders, views, signups).
     *
     * @param  Builder<covariant Model>  $query
     * @return Collection<int, array{label: string, short: string, value: float}>
     */
    public static function series(Builder $query, DateRange $range, ?string $sumColumn = null, string $column = 'created_at'): Collection
    {
        $buckets = self::buckets($range, $column);
        $granularity = $range->granularity();

        $rows = $range->apply($query, $column)
            ->reorder()
            ->get(array_values(array_unique([$column, ...array_filter([$sumColumn])])));

        $totals = $rows
            ->groupBy(fn (Model $row) => self::bucketKey($row->{$column}, $granularity))
            ->map(fn (Collection $group) => $sumColumn
                ? (float) $group->sum($sumColumn)
                : (float) $group->count());

        return $buckets->map(fn (array $bucket) => [
            'label' => $bucket['label'],
            'short' => $bucket['short'],
            'value' => (float) ($totals[$bucket['key']] ?? 0),
        ]);
    }

    /**
     * Every bucket the range covers, oldest first. "All time" has no start, so
     * it is charted from the first row's month — the caller passes a range with
     * a real start when it wants a fixed axis.
     *
     * @return Collection<int, array{key: string, label: string, short: string}>
     */
    private static function buckets(DateRange $range, string $column): Collection
    {
        $granularity = $range->granularity();

        // Never plot into the future: a window that ends today would otherwise
        // trail off through hours that have not happened yet.
        $end = $range->end()->min(now());
        $cursor = $range->start() ?? $end->copy()->subMonths(11);

        $cursor = match ($granularity) {
            'month' => $cursor->startOfMonth(),
            'week' => $cursor->startOfWeek(),
            'hour' => $cursor->startOfHour(),
            default => $cursor->startOfDay(),
        };

        $buckets = collect();

        while ($cursor->lessThanOrEqualTo($end)) {
            $buckets->push([
                'key' => self::bucketKey($cursor, $granularity),
                'label' => match ($granularity) {
                    'month' => $cursor->format('M Y'),
                    'week' => 'w/c '.$cursor->format('j M'),
                    'hour' => $cursor->format('H:00'),
                    default => $cursor->format('j M'),
                },
                'short' => match ($granularity) {
                    'month' => mb_substr($cursor->format('M'), 0, 1),
                    'week' => $cursor->format('j'),
                    'hour' => $cursor->format('H'),
                    default => mb_substr($cursor->format('D'), 0, 1),
                },
            ]);

            $cursor = match ($granularity) {
                'month' => $cursor->copy()->addMonth(),
                'week' => $cursor->copy()->addWeek(),
                'hour' => $cursor->copy()->addHour(),
                default => $cursor->copy()->addDay(),
            };
        }

        return $buckets;
    }

    private static function bucketKey(Carbon $date, string $granularity): string
    {
        return match ($granularity) {
            'month' => $date->format('Y-m'),
            'week' => $date->copy()->startOfWeek()->toDateString(),
            'hour' => $date->format('Y-m-d H'),
            default => $date->toDateString(),
        };
    }
}

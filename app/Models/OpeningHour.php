<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * The shop's week, one row per ISO weekday. Everything that shows hours —
 * footer, contact, About, /llms.txt and the OnlineStore schema — reads it
 * through lines() or specification(), so they can never drift apart.
 */
class OpeningHour extends Model
{
    public const DAYS = [
        1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday',
        5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday',
    ];

    protected $fillable = ['day', 'opens_at', 'closes_at', 'is_closed'];

    protected function casts(): array
    {
        return [
            'day' => 'integer',
            'is_closed' => 'boolean',
        ];
    }

    public function getNameAttribute(): string
    {
        return self::DAYS[$this->day] ?? '';
    }

    public function isOpen(): bool
    {
        return ! $this->is_closed && $this->opens_at && $this->closes_at;
    }

    /**
     * All seven days in order; a day with no row reads as closed.
     *
     * @return Collection<int, OpeningHour>
     */
    public static function week(): Collection
    {
        return once(function () {
            $rows = static::query()->get()->keyBy('day');

            return collect(self::DAYS)->map(
                fn ($name, $day) => $rows->get($day) ?? new static(['day' => $day, 'is_closed' => true])
            );
        });
    }

    /**
     * Consecutive days sharing the same hours, as display lines:
     * "Monday, 3pm–8pm", "Tuesday–Saturday, 10:30am–8pm", "Sunday, closed".
     *
     * @return array<int, string>
     */
    public static function lines(): array
    {
        return array_map(function (array $run) {
            $first = $run[0];
            $days = count($run) > 1 ? $first->name.'–'.end($run)->name : $first->name;

            return $days.', '.($first->isOpen()
                ? self::clock($first->opens_at).'–'.self::clock($first->closes_at)
                : 'closed');
        }, self::runs());
    }

    /**
     * schema.org OpeningHoursSpecification nodes; closed days are left out.
     *
     * @return array<int, array<string, mixed>>|null
     */
    public static function specification(): ?array
    {
        $out = [];

        foreach (self::runs() as $run) {
            if (! $run[0]->isOpen()) {
                continue;
            }

            $out[] = [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => array_map(fn ($d) => $d->name, $run),
                'opens' => $run[0]->opens_at,
                'closes' => $run[0]->closes_at,
            ];
        }

        return $out ?: null;
    }

    /** @return array<int, array<int, OpeningHour>> */
    private static function runs(): array
    {
        $runs = [];

        foreach (self::week() as $day) {
            $last = $runs ? $runs[array_key_last($runs)] : null;

            if ($last && self::signature(end($last)) === self::signature($day)) {
                $runs[array_key_last($runs)][] = $day;
            } else {
                $runs[] = [$day];
            }
        }

        return $runs;
    }

    private static function signature(self $day): string
    {
        return $day->isOpen() ? $day->opens_at.'-'.$day->closes_at : 'closed';
    }

    /** "15:00" → "3pm", "10:30" → "10:30am". */
    private static function clock(string $time): string
    {
        [$h, $m] = array_map('intval', explode(':', $time));
        $suffix = $h >= 12 ? 'pm' : 'am';
        $h = $h % 12 ?: 12;

        return $m ? sprintf('%d:%02d%s', $h, $m, $suffix) : $h.$suffix;
    }
}

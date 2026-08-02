<?php

namespace App\Console\Commands;

use App\Models\ProductEvent;
use App\Models\SiteEvent;
use Illuminate\Console\Command;

/**
 * `site_events` gains a row for every page anyone opens, which makes it the
 * fastest-growing table in the shop by a wide margin. This drops what is older
 * than the reports ever look at.
 *
 * Not scheduled by default — how much history is worth keeping is the shop's
 * call, not a default. Add it to routes/console.php when that is decided.
 */
class PruneSiteEvents extends Command
{
    protected $signature = 'site:prune-events
                            {--days=400 : Keep events newer than this many days}
                            {--chunk=5000 : Rows to delete per statement}
                            {--products : Prune product_events on the same cutoff too}
                            {--pretend : Report what would go, delete nothing}';

    protected $description = 'Delete site events older than the retention window';

    public function handle(): int
    {
        $days = max((int) $this->option('days'), 1);
        $cutoff = now()->subDays($days)->startOfDay();

        $this->line("Cutoff: {$cutoff->toDateTimeString()} (keeping {$days} days)");

        $total = $this->prune(SiteEvent::query()->where('created_at', '<', $cutoff), 'site_events');

        if ($this->option('products')) {
            $total += $this->prune(ProductEvent::query()->where('created_at', '<', $cutoff), 'product_events');
        }

        $this->info($this->option('pretend')
            ? number_format($total).' rows would be deleted.'
            : number_format($total).' rows deleted.');

        return self::SUCCESS;
    }

    /**
     * Deleted in chunks so a long-overdue first run cannot lock the table for
     * the length of one enormous statement.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     */
    private function prune($query, string $table): int
    {
        $count = (clone $query)->count();

        $this->line(sprintf('  %-16s %s rows', $table, number_format($count)));

        if ($this->option('pretend') || $count === 0) {
            return $count;
        }

        $chunk = max((int) $this->option('chunk'), 100);
        $deleted = 0;

        do {
            $just = (clone $query)->limit($chunk)->delete();
            $deleted += $just;
        } while ($just > 0);

        return $deleted;
    }
}

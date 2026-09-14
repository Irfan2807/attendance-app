<?php

namespace App\Console\Commands;

use App\Services\HolidayService;
use Illuminate\Console\Command;

class SyncHolidaysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holidays:sync {year? : The calendar year to sync (e.g. 2026)} {--state= : Specific state code (e.g. SGR)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and cache public holidays from the Malaysia Public Holiday API into the local database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $year = (int) ($this->argument('year') ?: now()->year);
        $state = $this->option('state');

        $this->info("Fetching public holidays for year {$year}" . ($state ? " (State: {$state})" : " (Nationwide)") . "...");

        $count = HolidayService::syncHolidays($year, $state);

        if ($count > 0) {
            $this->info("✓ Successfully synced and cached {$count} public holidays for {$year}.");
            return self::SUCCESS;
        }

        $this->warn("⚠ No holidays were synced. Please verify internet connectivity or API availability.");
        return self::FAILURE;
    }
}

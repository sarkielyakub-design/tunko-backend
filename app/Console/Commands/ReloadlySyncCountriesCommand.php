<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Reloadly\Sync\CountrySyncService;

class ReloadlySyncCountriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'reloadly:sync-countries';

    /**
     * The console command description.
     */
    protected $description = 'Synchronize countries from Reloadly';

    public function __construct(
        private readonly CountrySyncService $service
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $count = $this->service->sync();

        $this->info("Synced {$count} countries.");

        return self::SUCCESS;
    }
}
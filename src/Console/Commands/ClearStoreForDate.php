<?php

namespace Kregel\RedisStore\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Kregel\DataStore\Contracts\DataStoreContract;

class ClearStoreForDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:clear {date} {?endDate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear out the redis cache for a given date or date range';

    /**
     * @var DataStoreContract
     */
    protected $dataStore;

    /**
     * Create a new command instance.
     *
     * @param DataStoreContract $dataStore
     * @return void
     */
    public function __construct(DataStoreContract $dataStore)
    {
        parent::__construct();
        $this->dataStore = $dataStore;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (empty($this->argument('endDate'))) {
            $date = Carbon::parse($this->argument('date'))->format('Y-m-d');
            $this->info('Deleting items from the datastore for date '. $date);
            $this->dataStore->destroyTags([$this->dataStore::PACKAGE_TAG . ':' . $date]);
            return;
        }

        $startDate = Carbon::parse($this->argument('date'));
        $endDate = Carbon::parse(($this->argument('endDate')));

        $diffInDays = $startDate->diffInDays($endDate);

        for ($i = 0; $i < $diffInDays; $i ++) {
            $dateToBreak = $startDate->copy()->addDay($i);
            $this->info('Deleting items from the datastore for date '. $dateToBreak->format('Y-m-d'));
            $this->dataStore->destroyTags([
                $this->dataStore::PACKAGE_TAG . ':' . $dateToBreak->format('Y-m-d')
            ]);
        }
    }
}

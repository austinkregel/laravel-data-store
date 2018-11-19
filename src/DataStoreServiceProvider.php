<?php

declare(strict_types=1);

namespace Kregel\DataStore;

use Illuminate\Support\ServiceProvider;
use Kregel\DataStore\Contracts\DataStoreContract;

/**
 * Class DataStoreServiceProvider
 * @package Kregel\RedisStore
 */
class DataStoreServiceProvider extends ServiceProvider
{
    /**
     * Register services via the provider
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(DataStoreContract::class, DataStore::class);
    }
}

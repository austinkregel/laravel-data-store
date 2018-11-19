<?php

namespace Kregel\DataStore;

use Carbon\Carbon;
use Illuminate\Contracts\Redis\Factory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Kregel\DataStore\Contracts\DataStoreContract;

/**
 * Class Redis
 * @package Kregel\DataStore
 */
class DataStore implements DataStoreContract
{
    /**
     * @var Factory
     */
    protected $redis;

    /**
     * @var string
     */
    protected $model;

    /**
     * @var string
     */
    protected $prefix = '';

    /**
     * @var array
     */
    protected $otherTags = [];

    /**
     * Redis constructor.
     * @param Factory $redisFactory
     */
    public function __construct(Factory $redisFactory)
    {
        $this->redis = $redisFactory;
    }

    /**
     * @param string $model
     * @return DataStore
     */
    public static function forModel(string $model): DataStoreContract
    {
        return app(static::class)->model($model);
    }

    /**
     * @param string $model
     * @return DataStore
     */
    public function model(string $model): DataStoreContract
    {
        $this->model = $model;

        if (empty($this->prefix)) {
            $this->usePrefix(static::PACKAGE_TAG);
        }

        return $this;
    }

    /**
     * Set the prefix for this store to use
     * @param string $prefix
     * @return DataStore
     */
    public function usePrefix(string $prefix): DataStoreContract
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * @param string $key
     * @return array
     * @throws ModelNotFoundException
     */
    public function get(string $key): array
    {
        $key = $this->getKey($key);

        if (!cache()->has($key)) {
            throw new ModelNotFoundException(sprintf('No query results for the redis key [%s]', $key));
        }

        $value = cache()->get($key);

        if (empty($value)) {
            return [];
        }

        if (!is_array($value)) {
            return [$value];
        }

        return $value;
    }

    public function getKey(string $key): string
    {
        return $this->prefix . '.' . $key;
    }

    /**
     * Grab the first bit of data of whatever is saved to the key.
     * @param string $key
     * @return mixed|null
     */
    public function first(string $key)
    {
        $key = $this->getKey($key);

        if (!cache()->has($key)) {
            throw new ModelNotFoundException(sprintf('No query results for the redis key [%s]', $key));
        }

        $value = cache()->get($key);

        if (is_array($value)) {
            return Arr::first($value);
        }

        return $value;
    }

    /**
     * Save the results of the callback to the cache.
     * @param string $key
     * @param callable $callback
     * @return mixed
     * @throws ModelNotFoundException
     */
    public function save(string $key, callable $callback)
    {
        $key = $this->getKey($key);

        return cache()->tags(array_merge([
            // Tie the cache to the whole implementation.
            static::PACKAGE_TAG,
            // Save the timestamped version of data for both historical reasons, cache breaking reasons.
            static::PACKAGE_TAG . ':' . Carbon::now()->format('Y-m-d'),
            // Tie the cache to our model
            static::PACKAGE_TAG . ':' . $this->model,
            // Tie the cache to our model and current time.
            $key,
        ], $this->otherTags))->rememberForever($key, $callback);
    }

    /**
     * Checks if the thing exists in the store
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool
    {
        $key = $this->getKey($key);

        return cache()->tags(array_merge([
            // Tie the cache to the whole implementation.
            static::PACKAGE_TAG,
            // Save the timestamped version of data for both historical reasons, cache breaking reasons.
            static::PACKAGE_TAG . ':' . Carbon::now()->format('Y-m-d'),
            // Tie the cache to our model
            static::PACKAGE_TAG . ':' . $this->model,
            // Tie the cache to our model and current time.
            $key,
        ], $this->otherTags))->has($key);
    }

    /**
     * Remove an item from the store
     * @param string $key
     * @return void
     */
    public function destroy(string $key): void
    {
        $key = $this->getKey($key);

        cache()->tags(array_merge([
            // Tie the cache to the whole implementation.
            static::PACKAGE_TAG,
            // Save the timestamped version of data for both historical reasons, cache breaking reasons.
            static::PACKAGE_TAG . ':' . Carbon::now()->format('Y-m-d'),
            // Tie the cache to our model
            static::PACKAGE_TAG . ':' . $this->model,
            // Tie the cache to our model and current time.
            $key,
        ], $this->otherTags))->forget($key);
    }

    /**
     * Remove an item from the store
     * @param array $tags
     * @return void
     */
    public function destroyTags(array $tags): void
    {
        cache()->tags($tags)->flush();
    }
}

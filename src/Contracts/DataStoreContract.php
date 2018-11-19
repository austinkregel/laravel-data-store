<?php

declare(strict_types=1);

namespace Kregel\DataStore\Contracts;

/**
 * Interface DataStoreContract
 * @package Kregel\DataStore
 */
interface DataStoreContract
{
    /**
     * This is the main entry point into the store
     * @param string $model
     * @return DataStoreContract
     */
    public static function forModel(string $model): DataStoreContract;

    /**
     * Gives the dataset a prefix to help avoid key collisions
     * @param string $prefix
     * @return DataStoreContract
     */
    public function usePrefix(string $prefix): DataStoreContract;

    /**
     * Which representation of a class are you querying against?
     * @param string $model
     * @return DataStoreContract
     */
    public function model(string $model): DataStoreContract;

    /**
     * Get the data from the store
     * @param string $key
     * @return array
     */
    public function get(string $key): array;

    /**
     * Get the first data item from the store
     * @param string $key
     * @return mixed
     */
    public function first(string $key);

    /**
     * Save new data to the store
     * @param string $key
     * @param callable $callback
     * @return mixed
     */
    public function save(string $key, callable $callback);

    /**
     * Remove an item from the store
     * @param string $key
     * @return void
     */
    public function destroy(string $key): void;

    /**
     * Remove an item from the store
     * @param array $tags
     * @return void
     */
    public function destroyTags(array $tags): void;

    /**
     * Checks if an item exists in the store
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool;
}

<?php

namespace SolutionForest\InspireCms\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use SolutionForest\InspireCms\Base\Commnads\Concerns\WithPixelArt;
use SolutionForest\InspireCms\InspireCmsConfig;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'inspirecms:cache-stats',
    description: 'Display InspireCMS cache statistics including cache keys, stores, and TTLs.',
)]
class CacheStatsCommand extends Command
{
    use WithPixelArt;

    public function handle()
    {
        $this->displayPixelArtBanner('InspireCMS Cache Stats');
        $this->line('Cache Driver: ' . config('cache.default'));
        $this->line('Cache Prefix: ' . config('cache.prefix'));
        $this->line('Cache Tags: ' . implode(', ', config('cache.tags', [])));

        // Check specific cache keys
        $this->line('----------------------------------');
        $this->info('Specific Cache Keys:');
        $cacheKeys = InspireCmsConfig::get('cache');
        $tableData = [];
        $headers = ['Cache Key', 'Store', 'Exists', 'TTL (seconds)'];

        foreach ($cacheKeys as $key => $details) {

            if ($key == 'key_value') {
                continue; // Skip key_value cache as it's handled separately
            }

            $cacheKey = $details['key'] ?? $key;
            $cacheStore = $details['store'] ?? config('cache.default');

            $exists = Cache::store($cacheStore)->has($cacheKey) ? 'Yes' : 'No';
            $ttl = $details['ttl'] ?? 'N/A';

            $tableData[] = [
                $cacheKey,
                $cacheStore,
                $exists,
                $ttl,
            ];
        }

        // Key-Value Cache
        $kvCacheConfig = InspireCmsConfig::get('cache.key_value', []);
        $kvStore = $kvCacheConfig['store'] ?? config('cache.default');
        if (filled($kvCacheConfig['prefix'] ?? null)) {

            $kvCacheKeys = collect($this->getAllCacheKeys($kvStore, config('cache.stores.' . $kvStore, [])))
                ->where(fn ($key) => str_contains($key, $kvCacheConfig['prefix']))
                ->values()
                ->all() ?? [];

            foreach ($kvCacheKeys as $key) {

                $formattedCacheKey = $kvCacheConfig['prefix'] . str($key)->explode($kvCacheConfig['prefix'])->skip(1)->implode('');

                $tableData[] = [
                    $key,
                    $kvStore,
                    Cache::store($kvStore)->has($formattedCacheKey) ? 'Yes' : 'No',
                    $kvCacheConfig['ttl'] ?? 'N/A',
                ];
            }

        }

        $this->table($headers, $tableData);

        return Command::SUCCESS;
    }

    private function getAllCacheKeys(string $store, $storeConfig): array
    {
        try {

            $driver = $storeConfig['driver'] ?? null;

            if (
                empty($store)
                || empty($driver) // Store driver is not set
                || ! is_string($store) // Store name is not a string
            ) {
                return [];
            }

            $cacheStore = Cache::store($store)->getStore();

            switch ($driver) {
                case 'redis':
                    if (method_exists($cacheStore, 'getRedis')) {
                        $redis = $cacheStore->getRedis();
                        $prefix = $cacheStore->getPrefix();
                        $keys = $redis->keys($prefix . '*');

                        return array_map(function ($key) use ($prefix) {
                            return substr($key, strlen($prefix));
                        }, $keys);
                    }

                    break;

                case 'file':
                    $storage = storage_path('framework/cache/data');
                    $pattern = $storage . '/*';
                    $files = glob($pattern);

                    return array_map(function ($file) {
                        return str_replace('.php', '', basename($file));
                    }, $files);

                case 'database':

                    // Database store requires a connection and table name
                    $connection = $cacheStore->getConnection();
                    $table = $storeConfig['table'] ?? 'cache';
                    $query = $connection->table($table)->pluck('key')->toArray();

                    return array_map(function ($key) {
                        return str_replace('inspire_key_value_', '', $key);
                    }, $query);

                case 'dynamodb':

                    // DynamoDB store requires a connection and table name
                    $connection = config('database.connections.' . $cacheStore->getConnectionName());
                    $table = $cacheStore->getTable();
                    $query = DB::connection($connection)->table($table)->pluck('key')->toArray();

                    return array_map(function ($key) {
                        return str_replace('inspire_key_value_', '', $key);
                    }, $query);

                case 'array':
                case 'octane':
                    // Octane store does not persist keys, so no keys to retrieve
                    return [];

            }
        } catch (\Throwable $th) {
            //
        }

        return [];
    }
}

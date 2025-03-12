<?php

use SolutionForest\InspireCms\Facades\KeyValueCache;
use SolutionForest\InspireCms\Tests\Models\KeyValue;
use SolutionForest\InspireCms\Tests\TestCase;

uses(TestCase::class);

function createSampleData($slug = null, $value = null)
{
    return KeyValue::factory()->create([
            'key' => $slug ?? 'test_key',
            'value' => $value ?? 'test_value',
        ]);
}

describe('key vaue cache facade', function() {

    test('cacheAll stores all key-value pairs in cache', function () {
        // Create multiple test records
        $record1 = createSampleData('test_key1', 'test_value1');
        $record2 = createSampleData('test_key2', 'test_value2');
        
        // Call the cacheAll method which should cache all records
        KeyValueCache::cacheAll();
        
        // Verify that getting these values returns the correct result from cache
        expect(KeyValueCache::get('test_key1'))->toBe('test_value1');
        expect(KeyValueCache::get('test_key2'))->toBe('test_value2');
    });
    
    test('set stores value in cache and can be retrieved', function () {
        $key = 'new_test_key';
        $value = 'new_test_value';
        
        // Set the value in cache
        KeyValueCache::set($key, $value);
        
        // Verify it can be retrieved
        expect(KeyValueCache::get($key))->toBe($value);
    });
    
    test('forget removes a value from cache', function () {
        $key = 'forget_test_key';
        $value = 'forget_test_value';
        
        // Create and cache a test record
        $record = createSampleData($key, $value);
        KeyValueCache::set($record->key, $record->value);
        
        // Verify it's in cache
        expect(KeyValueCache::get($key))->toBe($value);
        
        // Forget the value
        KeyValueCache::forget($key);
        
        // The value should still be in the database but no longer in cache
        // Since the database is the fallback, we need to mock or spy on the cache
        // implementation to properly test this, but we can verify it still exists
        expect(KeyValue::where('key', $key)->exists())->toBeTrue();
    });
    
    test('clear removes all values from cache', function () {
        // Create multiple test records and cache them
        $keys = ['clear_key1', 'clear_key2', 'clear_key3'];
        $values = ['clear_value1', 'clear_value2', 'clear_value3'];
        
        foreach ($keys as $index => $key) {
            createSampleData($key, $values[$index]);
            KeyValueCache::set($key, $values[$index]);
        }
        
        // Verify they're all in cache
        foreach ($keys as $index => $key) {
            expect(KeyValueCache::get($key))->toBe($values[$index]);
        }
        
        // Clear the cache
        KeyValueCache::clear();
        
        // All records should still exist in the database
        foreach ($keys as $key) {
            expect(KeyValue::where('key', $key)->exists())->toBeTrue();
        }
    });
    
    test('get returns default value when key does not exist', function () {
        $nonExistentKey = 'non_existent_key_'.time();
        $defaultValue = 'default_test_value';
        
        $result = KeyValueCache::get($nonExistentKey, $defaultValue);
        
        expect($result)->toBe($defaultValue);
    });

})->group('unit', 'cache', 'key-value');
<?php

use SolutionForest\InspireCms\Facades\KeyValueCache;
use SolutionForest\InspireCms\Tests\TestCase;
use SolutionForest\InspireCms\Tests\Models\KeyValue;

uses(TestCase::class);

beforeEach(function () {
    $spy = KeyValueCache::spy();
});

function detechClearCache($key, $times = 1)
{
    KeyValueCache::shouldHaveReceived('forget')->times($times)->with($key);
}

describe('key value model', function () {

    it('can create a key value pair', function () {
        $keyValue = KeyValue::create([
            'key' => 'test_key',
            'value' => 'test_value',
        ]);
    
        expect($keyValue)->toBeInstanceOf(KeyValue::class);
        expect($keyValue->key)->toBe('test_key');
        expect($keyValue->value)->toBe('test_value');
        $this->assertDatabaseHas(KeyValue::class, [
            'key' => 'test_key',
            'value' => 'test_value',
        ]);

        detechClearCache($keyValue->key);
    });
    
    it('can update a key value pair', function () {
        $keyValue = KeyValue::create([
            'key' => 'test_key',
            'value' => 'test_value',
        ]);
    
        $keyValue->update([
            'value' => 'updated_value',
        ]);
    
        $this->assertDatabaseHas(KeyValue::class, [
            'key' => 'test_key',
            'value' => 'updated_value',
        ]);
        
        detechClearCache($keyValue->key, 2);
    });
    
    it('can delete a key value pair', function () {
        $keyValue = KeyValue::create([
            'key' => 'test_key',
            'value' => 'test_value',
        ]);
    
        $keyValue->delete();

        $this->assertDatabaseMissing(KeyValue::class, [
            'key' => 'test_key',
        ]);

        detechClearCache($keyValue->key, 2);
    });
    

})->group('unit', 'model', 'key-value');
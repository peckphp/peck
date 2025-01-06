<?php

declare(strict_types=1);

use Peck\Cache\CacheManager;

beforeEach(function (): void {
    $this->cacheManager = CacheManager::create('Peck.CacheManagerTest');
});

afterAll(function (): void {
    $this->cacheManager->clear();
});

it('can set and retrieve cached data', function (): void {
    $key = 'test_key';
    $value = ['data' => 'test_value'];

    // Set the cache
    $this->cacheManager->set($key, $value);

    // Retrieve the cache
    $cachedValue = $this->cacheManager->get($key);

    expect($cachedValue)->toBe($value);
});

it('returns null for a non-existent cache key', function (): void {
    $cachedValue = $this->cacheManager->get('non_existent_key');

    expect($cachedValue)->toBeNull();
});

it('can delete a cached item', function (): void {
    $key = 'delete_key';
    $value = 'value_to_delete';

    // Set the cache
    $this->cacheManager->set($key, $value);

    // Delete the cache
    $this->cacheManager->delete($key);

    // Retrieve the cache
    $cachedValue = $this->cacheManager->get($key);

    expect($cachedValue)->toBeNull();
});

it('can clear all cached items', function (): void {
    $this->cacheManager->set('key1', 'value1');
    $this->cacheManager->set('key2', 'value2');

    // Clear the cache
    $this->cacheManager->clear();

    // Check if the cache is empty
    $cachedValue1 = $this->cacheManager->get('key1');
    $cachedValue2 = $this->cacheManager->get('key2');

    expect($cachedValue1)->toBeNull();
    expect($cachedValue2)->toBeNull();
});

it('can handle cache expiration', function (): void {
    $key = 'expiring_key';
    $value = 'expiring_value';

    // Set the cache with a short lifetime
    $this->cacheManager->set($key, $value, 1);

    // Wait for the cache to expire
    sleep(2);

    // Retrieve the cache
    $cachedValue = $this->cacheManager->get($key);

    expect($cachedValue)->toBeNull();
});

<?php

declare(strict_types=1);

use Peck\Plugins\Cache;

it('should have a blank cache', function (): void {
    $cache = Cache::default();

    $key = uniqid();

    expect($cache->get($key))->toBeNull()
        ->and($cache->has($key))->toBeFalse();
});

it('should set and get a value', function (): void {
    $cache = Cache::default();

    $key = uniqid();

    $cache->set($key, 'value');

    expect($cache->get($key))->toBe('value')
        ->and($cache->has($key))->toBeTrue();
});

it('should be possible to use other cache directories', function (): void {
    $dir = __DIR__.'/../../.peck-test.cache';

    if (is_dir($dir)) {
        foreach (array_diff(scandir($dir), ['.', '..']) as $file) {
            unlink("{$dir}/{$file}");
        }

        rmdir($dir);
    }

    $cache = new Cache($dir);

    $cache->set('key', 'value');

    expect($cache->get('key'))->toBe('value')
        ->and($cache->has('key'))->toBeTrue();
});

it('throws an exception when the cache directory cannot be created', function (): void {
    $dir = '/root/.peck-test.cache';

    (new Cache($dir))->set('key', 'value');
})->throws(RuntimeException::class);

it('should return null when cache file exists but is not readable', function (): void {
    $dir = __DIR__.'/../../.peck-test.cache';

    if (is_dir($dir)) {
        foreach (array_diff(scandir($dir), ['.', '..']) as $file) {
            unlink("{$dir}/{$file}");
        }

        rmdir($dir);
    }

    $cache = new Cache($dir);

    $key = uniqid();

    $cache->set($key, 'value');

    $key = $cache->getCacheKey($key);
    chmod($cache->getCacheFile($key), 0);

    expect($cache->get($key))->toBeNull();
});

it('should return null if unserializedContents is false', function (): void {
    $cache = Cache::default();

    $key = uniqid();

    $cache->set($key, 'test');

    file_put_contents($cache->getCacheFile($cache->getCacheKey($key)), 'invalid serialized string');

    expect($cache->get($key))->toBeNull();
});

it('should return false if unserializedContents is false and serializedContents is b:0;', function (): void {
    $cache = Cache::default();

    $key = uniqid();

    $cache->set($key, false);

    expect($cache->get($key))->toBeFalse();
});

it('should return null if serializedContents is false', function (): void {
    $cache = Cache::default();

    $key = uniqid();

    $cache->set($key, 'test');

    file_put_contents($cache->getCacheFile($cache->getCacheKey($key)), false);

    expect($cache->get($key))->toBeNull();
});

it('can flush the cache', function (): void {
    $dir = __DIR__.'/../../.peck-test-flush.cache';

    if (is_dir($dir)) {
        foreach (array_diff(scandir($dir), ['.', '..']) as $file) {
            unlink("{$dir}/{$file}");
        }

        rmdir($dir);
    }

    $cache = new Cache($dir);

    $cache->set('key', 'value');

    expect($cache->get('key'))->toBe('value')
        ->and($cache->has('key'))->toBeTrue();

    $cache->flush();

    expect($cache->get('key'))->toBeNull();

});

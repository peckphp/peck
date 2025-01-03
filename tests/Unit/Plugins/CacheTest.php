<?php

declare(strict_types=1);

use Peck\Plugins\Cache;

use function Safe\rmdir;
use function Safe\scandir;

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
    new Cache('/root');
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

    chmod($cache->getCacheFile($key), 0);

    expect($cache->get($key))->toBeNull();
});

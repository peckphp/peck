<?php

declare(strict_types=1);

use Peck\Cache;

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

    foreach (array_diff(scandir($dir), ['.', '..']) as $file) {
        unlink("{$dir}/{$file}");
    }

    rmdir($dir);

    $cache = new Cache($dir);

    $cache->set('key', 'value');

    expect($cache->get('key'))->toBe('value')
        ->and($cache->has('key'))->toBeTrue();
});
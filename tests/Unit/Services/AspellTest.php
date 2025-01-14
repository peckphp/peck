<?php

declare(strict_types=1);

use Peck\Config;
use Peck\Plugins\Cache;
use Peck\Services\Spellcheckers\Aspell;

it('does not detect issues', function (): void {
    $spellchecker = Aspell::default();

    $issues = $spellchecker->check('Hello viewers');

    expect($issues)->toBeEmpty();
});

it('detects issues', function (): void {
    $spellchecker = Aspell::default();

    $issues = $spellchecker->check('Hello viewerss');

    expect($issues)->toHaveCount(1)
        ->and($issues[0]->word)->toBe('viewerss')
        ->and($issues[0]->suggestions)->toBe([
            'viewers',
            'viewer',
            'viewed',
            'veers',
        ]);
});

it('ignores case', function (): void {
    $spellchecker = Aspell::default();

    $issues = $spellchecker->check('english');

    expect($issues)->toBeEmpty();
});

it('detects issues that always don\'t have cache', function (): void {
    $dir = __DIR__.'/../../.peck-test.cache';

    if (! is_dir($dir)) {
        mkdir($dir);
    }

    $spellchecker = new Aspell(
        Config::instance(),
        new Cache($dir),
    );

    $cacheKey = md5('viewerss');

    if (is_link("$dir/{$cacheKey}")) {
        unlink("$dir/{$cacheKey}");
    }

    sleep(1); // Sometimes the cache is not deleted in time

    $issues = $spellchecker->check('Hello viewerss');

    expect($issues)->toHaveCount(1)
        ->and($issues[0]->word)->toBe('viewerss')
        ->and($issues[0]->suggestions)->toBe([
            'viewers',
            'viewer',
            'viewed',
            'veers',
        ]);
});

it('gets correct issues with corrupted cache', function (): void {
    $dir = __DIR__.'/../../.peck-test.cache';

    if (! is_dir($dir)) {
        mkdir($dir);
    }

    $cache = new Cache($dir);
    $spellchecker = new Aspell(
        Config::instance(),
        $cache,
    );

    // Let's corrupt the cache
    $cacheKey = 'Hello my viwers';
    $cacheFile = $cache->getCacheFile($cache->getCacheKey($cacheKey));
    file_put_contents($cacheFile, 'corrupted');

    $issues = $spellchecker->check($cacheKey);

    expect($issues)->not->toBeEmpty();
});

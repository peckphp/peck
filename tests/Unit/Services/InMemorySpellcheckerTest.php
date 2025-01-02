<?php

declare(strict_types=1);

use Peck\Cache;
use Peck\Config;
use Peck\Services\Spellcheckers\InMemorySpellchecker;
use PhpSpellcheck\Spellchecker\Aspell;

it('does not detect issues', function (): void {
    $spellchecker = InMemorySpellchecker::default();

    $issues = $spellchecker->check('Hello viewers');

    expect($issues)->toBeEmpty();
});

it('detects issues', function (): void {
    $spellchecker = InMemorySpellchecker::default();

    $issues = $spellchecker->check('Hello viewerss');

    expect($issues)->toHaveCount(1)
        ->and($issues[0]->word)->toBe('viewerss')
        ->and($issues[0]->suggestions)->toBe([
            'viewers',
            'viewer\'s',
            'viewer',
            'viewed',
        ]);
});

it('detects issues that always don\'t have cache', function (): void {
    $dir = __DIR__.'/../../.peck-test.cache';

    if (! is_dir($dir)) {
        mkdir($dir);
    }

    $spellchecker = new InMemorySpellchecker(
        Config::instance(),
        Aspell::create(),
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
            'viewer\'s',
            'viewer',
            'viewed',
        ]);
});

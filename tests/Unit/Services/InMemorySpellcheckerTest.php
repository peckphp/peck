<?php

declare(strict_types=1);

use Peck\Cache\CacheManager;
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

it('caches the issues', function (): void {
    $spellchecker = new InMemorySpellchecker(
        Config::instance(),
        Aspell::create(),
        CacheManager::create(
            namespace: 'Peck.CacheManagerTest',
            cacheDirectory: __DIR__.'/../../../.peck.cache'
        )
    );

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

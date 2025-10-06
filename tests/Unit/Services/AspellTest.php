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

it('ignores currency codes', function (): void {
    $spellchecker = Aspell::default();

    $issues = $spellchecker->check('USD is the currency code for United States Dollar, while EUR is for Euro, and BRL is for Brazilian Real.');

    expect($issues)->toBeEmpty();
});

it('ignores alpha-2 country codes', function (): void {
    $spellchecker = Aspell::default();

    $issues = $spellchecker->check('US is the country code for United States, while DE is for Germany, and BR is for Brazil.');

    expect($issues)->toBeEmpty();
});

it('ignores alpha-3 country codes', function (): void {
    $spellchecker = Aspell::default();

    $issues = $spellchecker->check('USA is the country code for United States, while DEU is for Germany, and BRA is for Brazil.');

    expect($issues)->toBeEmpty();
});

it('ignores words for specific files', function (): void {
    $config = new Config([], [], [
        'src/Test.php' => ['testword'],
        'tests/SomeTest.php' => ['unittest'],
    ]);
    $cache = new Cache(__DIR__.'/../../.peck-test.cache');
    $spellchecker = new Aspell($config, $cache);

    // Should ignore 'testword' in src/Test.php
    $issues = $spellchecker->check('testword', 'src/Test.php');
    expect($issues)->toBeEmpty();

    // Should not ignore 'testword' in other files
    $issues = $spellchecker->check('testword', 'src/Other.php');
    expect($issues)->toHaveCount(1)
        ->and($issues[0]->word)->toBe('testword');

    // Should ignore 'unittest' in the specific test file
    $issues = $spellchecker->check('unittest', 'tests/SomeTest.php');
    expect($issues)->toBeEmpty();

    // Should not ignore 'unittest' in non-test files
    $issues = $spellchecker->check('unittest', 'src/Service.php');
    expect($issues)->toHaveCount(1)
        ->and($issues[0]->word)->toBe('unittest');
});

it('combines global and file-specific ignores', function (): void {
    $config = new Config(['globalword'], [], [
        'src/Test.php' => ['specificword'],
    ]);
    $cache = new Cache(__DIR__.'/../../.peck-test.cache');
    $spellchecker = new Aspell($config, $cache);

    // Global word should be ignored everywhere
    $issues = $spellchecker->check('globalword', 'src/Test.php');
    expect($issues)->toBeEmpty();

    $issues = $spellchecker->check('globalword', 'src/Other.php');
    expect($issues)->toBeEmpty();

    // File-specific word should only be ignored in that file
    $issues = $spellchecker->check('specificword', 'src/Test.php');
    expect($issues)->toBeEmpty();

    $issues = $spellchecker->check('specificword', 'src/Other.php');
    expect($issues)->toHaveCount(1)
        ->and($issues[0]->word)->toBe('specificword');
});

<?php

declare(strict_types=1);

use Peck\Config;

it('should have a default configuration', function (): void {
    $config = Config::instance();

    expect($config->whitelistedWords)->toBe([
        'php',
    ])->and($config->whitelistedPaths)->toBe([]);
});

it('should to be a singleton', function (): void {
    $configA = Config::instance();
    $configB = Config::instance();

    expect($configA)->toBe($configB);
});

it('should behave correctly even if the peck.json file does not exist', function (): void {
    Config::resolveConfigFilePathUsing(
        fn (): string => __DIR__.'/dummy-that-does-not-exist.json',
    );

    $config = Config::instance();

    expect($config->whitelistedWords)->toBe([])
        ->and($config->whitelistedPaths)->toBe([]);
});

it('should be able to create a peck.json config file', function (): void {
    if (file_exists($filePath = dirname(__DIR__).'/peck-new.json')) {
        unlink($filePath);
    }
    Config::flush();
    Config::resolveConfigFilePathUsing(
        fn (): string => $filePath,
    );

    $created = Config::init();
    $config = Config::instance();

    expect($created)->toBeTrue()
        ->and($config->whitelistedWords)->toBe(['php'])
        ->and($config->whitelistedPaths)->toBe([]);

    unlink($filePath);
});

it('should not recreate a file that already exists', function (): void {
    $created = Config::init();
    $config = Config::instance();

    expect($created)->toBeFalse()
        ->and($config->whitelistedWords)->toBe([
            'php',
        ])
        ->and($config->whitelistedPaths)->toBe([]);
});

it('can set ignore words', function (): void {
    if (file_exists($filePath = dirname(__DIR__).'/peck-testing.json')) {
        unlink($filePath);
    }
    Config::flush();
    Config::resolveConfigFilePathUsing(
        fn (): string => $filePath,
    );

    Config::init();
    $config = Config::instance();

    expect($config->whitelistedWords)->toBe(['php']);

    $config->ignoreWords(['laravel', 'paravel']);
    Config::flush();
    unlink($filePath);

    expect($config->whitelistedWords)->toBe(['php', 'laravel', 'paravel']);
});

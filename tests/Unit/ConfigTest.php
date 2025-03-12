<?php

declare(strict_types=1);

use Peck\Config;

it('should have a default configuration', function (): void {
    $config = Config::instance();

    expect($config->whitelistedWords)->toBe([
        'php',
    ])->and($config->whitelistedPaths)->toBe([
        'tests',
    ]);
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
    $configFilePath = __DIR__.'/../../peck.json';
    $backup = $configFilePath.'.backup';
    rename($configFilePath, $backup);

    $created = Config::init();
    $config = Config::instance();

    expect($created)->toBeTrue()
        ->and($config->whitelistedWords)->toBe(['php'])
        ->and($config->whitelistedPaths)->toBe([]);

    rename($backup, $configFilePath);
})->skip('rewrite this test a little bit differently without modifying the root level peck.json file');

it('should not recreate a file that already exists', function (): void {
    $created = Config::init();
    $config = Config::instance();

    expect($created)->toBeFalse()
        ->and($config->whitelistedWords)->toBe([
            'php',
        ])
        ->and($config->whitelistedPaths)->toBe([
            'tests',
        ]);
});

it('should throw an runtime exception if the presets are not an array', function (): void {
    Config::resolveConfigFilePathUsing(
        fn (): string => 'tests/Fixtures/invalid-presets-peck.json',
    );

    Config::instance();
})->throws(RuntimeException::class, 'The presets must be an array with all the presets you want to use.');

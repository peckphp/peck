<?php

declare(strict_types=1);

use Peck\Config;

it('should have a default configuration', function (): void {
    $config = Config::instance();

    expect($config->whitelistedWords)->toBe([
        'config',
        'aspell',
        'args',
        'namespace',
        'doc',
        'bool',
        'php',
        'api',
        'enum',
        'enums',
        'backend',
    ])->and($config->whitelistedDirectories)->toBe([]);
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
        ->and($config->whitelistedDirectories)->toBe([]);
});

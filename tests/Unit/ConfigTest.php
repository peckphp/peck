<?php

declare(strict_types=1);

use Peck\Config;
use Peck\Support\ProjectPath;

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

describe('language', function (): void {
    beforeEach(function (): void {
        $configPath = '/temp.json';
        $this->jsonPath = ProjectPath::get().$configPath;
        Config::flush();
        Config::resolveConfigFilePathUsing(fn (): string => $configPath);
        $this->exampleConfig = [
            'preset' => 'laravel',
            'ignore' => [
                'words' => [
                    'config',
                ],
            ],
        ];
    });

    it('should default to `en_US` when the language flag is not set in the json file', function (): void {
        file_put_contents($this->jsonPath, json_encode($this->exampleConfig));
        $config = Config::instance();
        expect($config->getLanguage())->toBe('en_US');
    });

    it('should read the language flag from the json file', function (): void {
        $this->exampleConfig['language'] = 'en_GB';
        file_put_contents($this->jsonPath, json_encode($this->exampleConfig));
        $config = Config::instance();
        expect($config->getLanguage())->toBe('en_GB');
    });

    afterEach(function (): void {
        unlink($this->jsonPath);
    });
});

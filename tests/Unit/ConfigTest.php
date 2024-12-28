<?php

use Peck\Config;

afterAll(closure: function (): void {
    $files = glob(__DIR__.'/../Fixtures/peck-*.json') ?: [];

    foreach ($files as $file) {
        unlink($file);
    }
});

it('should have a default configuration', function (): void {
    $config = Config::instance();

    expect($config->whitelistedWords)->toBe(['config', 'json'])
        ->and($config->whitelistedDirectories)->toBe([]);
});

it('should to be a singleton', function (): void {
    $configA = Config::instance();
    $configB = Config::instance();

    expect($configA)->toBe($configB);
});

it('returns the peck config file path with relative location', function (): void {
    $configFilepath = Config::instance()->getConfigFilePath();

    expect($configFilepath)->toBe(dirname(__DIR__, 2).'/peck.json');
});

it('returns the peck config file path from set value', function (): void {
    $configFilepath = Config::instance(
        refresh: true,
        configFilePath: $testPeck = __DIR__.'/../Fixtures/peck.json.stub'
    )->getConfigFilePath();

    expect($configFilepath)->toBe($testPeck);
});

it('returns empty if the config to array method fails', function (): void {
    touch($emptyJson = __DIR__.'/../Fixtures/peck-empty.json');
    expect(Config::instance(refresh: true, configFilePath: $emptyJson)->getConfigAsArray())->toBe([]);
});

it('can set a config variable', function (): void {
    file_put_contents(
        filename: $testPeck = __DIR__.'/../Fixtures/peck-'.random_int(1, 10000).'.json',
        data: file_get_contents(__DIR__.'/../Fixtures/peck.json.stub')
    );

    $configInstance = Config::instance(
        refresh: true,
        configFilePath: $testPeck,
    );
    $config = $configInstance->getConfigAsArray();

    expect($config['ignore']['words'])->toBe(['config']);

    $configInstance->set('ignore.words', $expected = ['config', 'http', 'php']);

    $configInstance2 = Config::instance(
        refresh: true,
        configFilePath: $testPeck,
    );

    expect($configInstance->getConfigAsArray()['ignore']['words'])->toBe($expected);
});

it('throws an exception when trying to set a config value that does not exist', function (): void {
    $configInstance = Config::instance(
        refresh: true,
        configFilePath: __DIR__.'/../Fixtures/peck.json.stub',
    );

    $configInstance->set('accept.words', $expected = ['config', 'http', 'php']);
})->throws(InvalidArgumentException::class);

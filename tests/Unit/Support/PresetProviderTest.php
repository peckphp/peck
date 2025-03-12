<?php

declare(strict_types=1);

use Peck\Support\PresetProvider;

it('returns only words from base preset when presets are not given', function (): void {
    expect(PresetProvider::whitelistedWords())->toBe(PresetProvider::getWordsFromStub('base'));
});

it('returns only words from base preset when all given presets are invalids', function (): void {
    expect(PresetProvider::whitelistedWords(['invalid-one', 'invalid-two']))->toBe(PresetProvider::getWordsFromStub('base'));
});

it('returns the whitelisted words for the given and base presets', function (): void {
    expect(PresetProvider::whitelistedWords(['laravel', 'iso3166', 'iso4217']))->toContain(
        'apa',
        'USD',
        'USA',
        'https'
    );
});

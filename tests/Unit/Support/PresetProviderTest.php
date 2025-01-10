<?php

declare(strict_types=1);

use Peck\Support\PresetProvider;

it('returns an empty array when the preset is null', function (): void {
    expect(PresetProvider::whitelistedWords(null))->toBe([]);
});

it('returns an empty array when the preset is invalid', function (): void {
    expect(PresetProvider::whitelistedWords('invalid'))->toBe([]);
});

it('returns the whitelisted words for the given preset', function (): void {
    expect(PresetProvider::whitelistedWords('laravel'))->toBe([
        'auth',
        'laravel',
    ]);
});

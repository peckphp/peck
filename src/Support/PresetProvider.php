<?php

declare(strict_types=1);

namespace Peck\Support;

/**
 * Simple helper to provide the whitelisted words for a given preset.
 * The whitelisted words are used to ignore certain words when spellchecking.
 */
final readonly class PresetProvider
{
    /**
     * The directory where the preset stubs are stored.
     */
    private const string PRESET_STUBS_DIRECTORY = __DIR__.'/../../stubs/presets';

    /**
     * Returns the whitelisted words for the given preset.
     *
     * @return array<int, string>
     */
    public static function whitelistedWords(?string $preset): array
    {
        if ($preset === null || ! self::stubExists($preset)) {
            return [];
        }

        return [...self::getWordsFromStub('base'), ...self::getWordsFromStub($preset)];
    }

    /**
     * Gets the words from the given stub.
     *
     * @return array<int, string>
     */
    private static function getWordsFromStub(string $preset): array
    {
        $path = sprintf('%s/%s.stub', self::PRESET_STUBS_DIRECTORY, $preset);

        return array_values(array_filter(array_map('trim', explode("\n", (string) file_get_contents($path)))));
    }

    /**
     * Checks if the given preset exists.
     */
    private static function stubExists(string $preset): bool
    {
        return file_exists(sprintf('%s/%s.stub', self::PRESET_STUBS_DIRECTORY, $preset));
    }
}

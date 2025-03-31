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
    private const PRESET_STUBS_DIRECTORY = __DIR__.'/../../stubs/presets';

    /**
     * Returns the whitelisted words for the given preset.
     *
     * @param  array<int, string>|null  $presets
     * @return array<int, string>
     */
    public static function whitelistedWords(?array $presets = []): array
    {
        /** @var array<int, string> */
        $words = [
            ...self::getWordsFromStub('base'),
        ];

        array_map(
            static function (string $preset) use (&$words): void {
                $words = [
                    ...$words,
                    ...self::getWordsFromStub($preset),
                ];
            },
            $presets ?? [],
        );

        return $words;
    }

    /**
     * Gets the words from the given stub.
     *
     * @return array<int, string>
     */
    public static function getWordsFromStub(string $preset): array
    {
        if (! self::stubExists($preset)) {
            return [];
        }

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

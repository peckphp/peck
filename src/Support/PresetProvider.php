<?php

declare(strict_types=1);

namespace Peck\Support;

/**
 * Simple helper to provide the whitelisted words for a given preset.
 * The whitelisted words are used to ignore certain words when spellchecking.
 */
final readonly class PresetProvider
{
    private const string PRESET_STUBS_DIRECTORY = '/../../stubs/presets';

    /**
     * Returns the whitelisted words for the given preset.
     */
    public static function whitelistedWords(?string $preset): array
    {
        if ($preset === null) {
            return [];
        }

        $path = self::getStubPath($preset);

        if (! file_exists($path)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode("\n", (string) file_get_contents($path)))));
    }

    private static function getStubPath(string $preset): string
    {
        return sprintf('%s/%s.stub', __DIR__.self::PRESET_STUBS_DIRECTORY, $preset);
    }
}

<?php

declare(strict_types=1);

namespace Peck\Support;

/**
 * Simple helper to provide the whitelisted words for a given preset.
 * The whitelisted words are used to ignore certain words when spellchecking.
 */
final readonly class PresetProvider
{
    private const string PRESET_STUBS_DIRECTORY = __DIR__.'/../../stubs/presets';

    /**
     * Returns the whitelisted words for the given preset.
     *
     * @return array<int, string>
     */
    public static function whitelistedWords(?string $preset): array
    {
        if ($preset === null) {
            return [];
        }

        $path = sprintf('%s/%s.stub', self::PRESET_STUBS_DIRECTORY, $preset);

        if (! file_exists($path)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode("\n", (string) file_get_contents($path)))));
    }
}

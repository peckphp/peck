<?php

declare(strict_types=1);

namespace Peck\Support;

final readonly class SpellcheckFormatter
{
    /**
     * Transforms the given input (method or class names) into a
     * human-readable format which can be used for spellchecking.
     */
    public static function format(string $input): string
    {
        // Trim leading underscores (e.g. __construct -> construct)
        $input = ltrim($input, '_');

        // Replace underscores and hyphens with spaces (for snake, screaming snake, and kebab case)
        $input = str_replace(['_', '-'], ' ', $input);

        // Add spaces before capital letters (for camel and pascal case)
        $input = (string) preg_replace('/([a-z])([A-Z])/', '$1 $2', $input);

        // Lowercase the input
        $input = strtolower($input);

        return $input;
    }
}

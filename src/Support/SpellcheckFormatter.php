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
        // Remove leading underscores
        $input = ltrim($input, '_');

        // Replace underscores and dashes with spaces
        $input = str_replace(['_', '-'], ' ', $input);

        // Insert spaces between lowercase and uppercase letters (camelCase or PascalCase)
        $input = (string) preg_replace('/([a-z])([A-Z])/', '$1 $2', $input);

        // Convert the final result to lowercase
        return strtolower($input);
    }
}

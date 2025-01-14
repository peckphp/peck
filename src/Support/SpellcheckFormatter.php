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

        // Replace special characters with spaces
        $input = (string) preg_replace('/[!@#$%^&<>():.|,\/\\\\_\-*]/', ' ', $input);

        // Insert spaces between lowercase and uppercase letters (camelCase or PascalCase)
        $input = (string) preg_replace('/([a-z])([A-Z])/', '$1 $2', $input);

        // Split sequences of uppercase letters, ensuring the last uppercase letter starts a new word
        $input = (string) preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1 $2', $input);

        // Replace multiple spaces with a single space
        $input = (string) preg_replace('/\s+/', ' ', $input);

        // Convert the final result to lowercase
        return trim(strtolower($input));
    }
}

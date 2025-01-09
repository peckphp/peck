<?php

declare(strict_types=1);

namespace Peck\Support;

final readonly class NameParser
{
    /**
     * Transforms the given input (method or class names) into a
     * human-readable format which can be used for spellchecking.
     */
    public static function parse(string $input): string
    {
        $trimmed = ltrim($input, '_');

        $dashed = str_replace(['_', '-'], ' ', $trimmed);

        // Add spaces before capital letters (for camel and pascal case)
        $words = (string) preg_replace('/([a-z])([A-Z])/', '$1 $2', $dashed);

        return strtolower($words);
    }
}

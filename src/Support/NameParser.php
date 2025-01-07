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
        return strtolower(
            (string) preg_replace(
                '/([a-z])([A-Z])/',
                '$1 $2',
                str_replace(['_', '-'], ' ', ltrim($input, '_'))
            )
        );
    }
}

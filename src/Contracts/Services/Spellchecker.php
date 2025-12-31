<?php

declare(strict_types=1);

namespace Peck\Contracts\Services;

use Peck\ValueObjects\Misspelling;

/**
 * @internal
 */
interface Spellchecker
{
    /**
     * Checks of issues in the given text.
     *
     * @return array<int, Misspelling>
     */
    public function check(string $text, ?string $filePath = null): array;
}

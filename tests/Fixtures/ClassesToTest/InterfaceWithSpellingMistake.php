<?php

declare(strict_types=1);

namespace Tests\Fixtures\ClassesToTest;

/**
 * Spellling mistake in the interface documentation.
 */
interface InterfaceWithSpellingMistake
{
    public function methodWithTypoErorr(): string;
}

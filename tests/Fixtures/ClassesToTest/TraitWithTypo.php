<?php

declare(strict_types=1);

namespace Tests\Fixtures\ClassesToTest;

/**
 * Trait TraitWithTypo
 *
 * This trait is used to tst type errors in trait properties, methods, method parameters and trait documentation block.
 *
 * @internal
 */
trait TraitWithTypo
{

    public int $propertyWithoutSpellingMistake = 1;

    public int $properytWithSpelllingMistake = 2;

    private function methodWithoutSpellingMistakeInName(): void
    {
        // This is a method without a spelling mistake.
    }

    private function methodWithSpelllingMistakeInName(): void
    {
        // This is a method with a spelling mistake.
    }

    private function methodWithSpellingMistakeInParameters(string $spelllingMistakeInParameter): void
    {
        // This is a method with a spelling mistake in parameters.
    }

    private function methodWithoutSpellingMistakeInParameters(string $noSpellingMistakeInParameter): void
    {
        // This is a method without a spelling mistake in parameters.
    }

    /**
     * This is a method with a spellling mistake in the doc block.
     */
    private function methodWithSpellingMistakeInDocBlock(): void
    {
        // This is a method with a spelling mistake in doc block.
    }
}

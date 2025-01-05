<?php

declare(strict_types=1);

namespace Tests\Fixtures\EnumsToTest;

/**
 * Even the documentation has spellling mistakes.
 */
enum UnitEnumWithTypoErrors
{
    case CASE_WITH_NO_SPELLING_MISTAKES;
    case CASE_WITH_SPELLLING_MISTAKE_IN_CASE_NAME;

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

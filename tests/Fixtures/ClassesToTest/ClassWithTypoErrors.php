<?php

declare(strict_types=1);

namespace Tests\Fixtures\ClassesToTest;

/**
 * Class ClassWithTypoErrors
 *
 * This class is used to tst type errors in class properties, methods, method parameters and class documentation block.
 *
 * @internal
 */
final class ClassWithTypoErrors implements InterfaceWithSpellingMistake
{
    public int $propertyWithoutTypoError = 1;

    public int $properytWithTypoError = 2;

    /**
     * This is a property with a doc bolck typo error
     */
    public int $propertyWithDocBlockTypoError = 3;

    public function methodWithoutTypoError(): string
    {
        return 'This is a method without a typo error';
    }

    public function methodWithTypoErorr(): string
    {
        return 'This is a method with a typo error';
    }

    /**
     * This is a metohd with a doc block typo error
     */
    public function methodWithDocBlockTypoError(): string
    {
        return 'This is a method with a doc block typo error';
    }

    public function methodWithTypoErrorInParameters(string $parameterWithoutTypoError, string $parameterWithTypoErorr): string
    {
        $parameterWithoutTypoError = 'Nuno Maduro is a good teatcher';
        $parameterWithTypoErorr = 'Nuno Maduro is an awsome teacher';

        return 'This is a method with a typo error in parameters';
    }

    public function methodWithoutTypoErrorInParameters(string $parameterWithoutTypoError): string
    {
        $parameterWithoutTypoError = 'Nuno Maduro is an awesome teacher';

        return 'This is a method without a typo error in parameters';
    }
}

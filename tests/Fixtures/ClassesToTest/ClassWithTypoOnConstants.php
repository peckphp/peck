<?php

declare(strict_types=1);

namespace Tests\Fixtures\ClassesToTest;

final class ClassWithTypoOnConstants
{
    public const CONSTANT_WITHOUT_TYPO = 'constant_without_typo';

    public const CONSTANT_WITH_TYPOO = 'constant_with_typoo';
}

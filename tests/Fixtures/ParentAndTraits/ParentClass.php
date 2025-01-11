<?php

declare(strict_types=1);

namespace Tests\Fixtures\ParentAndTraits;

abstract class ParentClass
{
    const PARENT_WITH_CONSTNAT_TYPE = 'parent_constantvaleu_typo';

    public string $parentProperttWithTypo = '';

    public function parentMetodWithTypo(): void
    {
        //
    }
}

<?php

declare(strict_types=1);

namespace Tests\Fixtures\ParentAndTraits;

trait UsedTrait
{
    const TRAIT_WITH_CONSTNAT_TYPE = 'trait_constantvaleu_typo';

    public string $traitProperttWithTypo = '';

    public function traitMetodWithTypo(): void
    {
        //
    }
}

<?php

declare(strict_types=1);

namespace Tests\Fixtures\ParentAndTraits;

final class ChildClass extends ParentClass
{
    use UsedTrait;
}

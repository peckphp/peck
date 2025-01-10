<?php

declare(strict_types=1);

namespace Tests\Fixtures\EnumsToTest\FolderThatShouldBeIgnored;

enum EnumWithTypoErrors
{
    case CASE_WITH_SPELLLING_MISTAKE_IN_CASE_NAME;
}

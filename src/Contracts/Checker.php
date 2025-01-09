<?php

declare(strict_types=1);

namespace Peck\Contracts;

use Peck\ValueObjects\Issue;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal
 */
interface Checker
{
    /**
     * Checks the given file for issues.
     *
     * @return array<int, Issue>
     */
    public function check(SplFileInfo $file): array;

    /**
     * Checks if the checker supports the given file.
     * (Some checkers may only support certain file types, e.g. PHP files).
     */
    public function supports(SplFileInfo $file): bool;
}

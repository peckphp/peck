<?php

declare(strict_types=1);

namespace Peck\Contracts;

use Peck\ValueObjects\Issue;

/**
 * @internal
 */
interface Checker
{
    /**
     * Checks of issues in the given text.
     *
     * @param  array{directory: string, onSuccess: callable(): void, onFailure: callable(): void}  $parameters
     * @return array<int, Issue>
     */
    public function check(array $parameters): array;
}

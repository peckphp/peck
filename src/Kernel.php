<?php

declare(strict_types=1);

namespace Peck;

use Peck\Checkers\ClassChecker;
use Peck\Checkers\FileSystemChecker;
use Peck\Services\Spellcheckers\InMemorySpellchecker;

final readonly class Kernel
{
    /**
     * Creates a new instance of Kernel.
     *
     * @param  array<int, Contracts\Checker>  $checkers
     */
    public function __construct(
        private array $checkers,
    ) {
        //
    }

    /**
     * Creates the default instance of Kernel.
     */
    public static function default(): self
    {
        $config = Config::instance();
        $inMemoryChecker = InMemorySpellchecker::default();

        return new self(
            [
                new FileSystemChecker($config, $inMemoryChecker),
                new ClassChecker($config, $inMemoryChecker),
            ],
        );
    }

    /**
     * Handles the given parameters.
     *
     * @param  array{directory?: string}  $parameters
     * @return array<int, ValueObjects\Issue>
     */
    public function handle(array $parameters): array
    {
        $issues = [];

        foreach ($this->checkers as $checker) {
            $issues = [
                ...$issues,
                ...$checker->check($parameters),
            ];
        }

        return $issues;
    }
}

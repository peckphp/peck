<?php

declare(strict_types=1);

namespace Peck;

use Peck\Checkers\FileSystemChecker;
use Peck\Checkers\SourceCodeChecker;
use Peck\Services\Spellcheckers\Aspell;

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
        $aspell = Aspell::default();

        return new self(
            [
                new FileSystemChecker($config, $aspell),
                new SourceCodeChecker($config, $aspell),
            ],
        );
    }

    /**
     * Handles the given parameters.
     *
     * @param  array{directory: string, onSuccess: callable(): void, onFailure: callable(): void}  $parameters
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

<?php

declare(strict_types=1);

namespace Peck;

use Peck\Checkers\FileSystemChecker;
use Peck\Console\Commands\DefaultCommand;
use Peck\Console\Commands\WhitelistCommand;
use Peck\Services\Spellcheckers\InMemorySpellchecker;
use Peck\Services\WhitelistManager;
use Symfony\Component\Console\Command\Command;

final readonly class Kernel
{
    /**
     * Creates a new instance of Kernel.
     *
     * @param  array<int, Contracts\Checker>  $checkers
     * @param  array<int, Command>  $commands
     */
    public function __construct(
        private array $checkers,
        private array $commands = [],
    ) {
        //
    }

    /**
     * Creates the default instance of Kernel.
     */
    public static function default(): self
    {
        $whitelistManager = new WhitelistManager(getcwd());
        $inMemoryChecker = InMemorySpellchecker::default();

        return new self(
            checkers: [
                new FileSystemChecker($inMemoryChecker),
            ],
            commands: [
                new DefaultCommand(),
                new WhitelistCommand($whitelistManager),
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

    /**
     * Get all registered commands.
     *
     * @return array<int, Command>
     */
    public function getCommands(): array
    {
        return $this->commands;
    }
}

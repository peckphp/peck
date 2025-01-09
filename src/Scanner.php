<?php

declare(strict_types=1);

namespace Peck;

use Peck\Checkers\ClassChecker;
use Peck\Checkers\FileSystemChecker;
use Peck\Contracts\Checker;
use Peck\Services\Spellcheckers\InMemorySpellchecker;
use Peck\ValueObjects\Issue;
use Symfony\Component\Finder\Finder;

final readonly class Scanner
{
    /**
     * @param  array<Checker>  $checkers
     */
    public function __construct(
        private Config $config,
        private array $checkers = []
    ) {}

    public static function default(): self
    {
        $inMemoryChecker = InMemorySpellchecker::default();

        return new self(
            Config::instance(),
            [
                new FileSystemChecker($inMemoryChecker),
                new ClassChecker($inMemoryChecker),
            ]
        );
    }

    /**
     * @param  array{directory: string}  $parameters
     * @return array<int, Issue>
     */
    public function scan(array $parameters): array
    {
        $filesOrDirectories = Finder::create()
            ->notPath($this->config->whitelistedDirectories)
            ->ignoreDotFiles(true)
            ->ignoreUnreadableDirs()
            ->ignoreVCSIgnored(true)
            ->in($parameters['directory'])
            ->getIterator();

        $issues = [];

        foreach ($filesOrDirectories as $fileOrDirectory) {
            foreach ($this->checkers as $checker) {

                if (! $checker->supports($fileOrDirectory)) {
                    $issues = [
                        ...$issues,
                        ...$checker->check($fileOrDirectory),
                    ];
                }
            }
        }

        return $issues;
    }
}

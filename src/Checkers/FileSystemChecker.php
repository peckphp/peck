<?php

declare(strict_types=1);

namespace Peck\Checkers;

use Peck\Config;
use Peck\Contracts\Checker;
use Peck\Contracts\Services\Spellchecker;
use Peck\Support\NotPaths;
use Peck\Support\SpellcheckFormatter;
use Peck\ValueObjects\Issue;
use Peck\ValueObjects\Misspelling;
use Symfony\Component\Finder\Finder;

/**
 * @internal
 */
final readonly class FileSystemChecker implements Checker
{
    /**
     * Creates a new instance of FileSystemChecker.
     */
    public function __construct(
        private Config $config,
        private Spellchecker $spellchecker,
    ) {}

    /**
     * Checks for issues in the given directory.
     *
     * @param  array{directory: string, onSuccess: callable(): void, onFailure: callable(): void}  $parameters
     * @return array<int, Issue>
     */
    public function check(array $parameters): array
    {
        $filesOrDirectories = iterator_to_array(Finder::create()
            ->notPath(NotPaths::get($parameters['directory'], $this->config->whitelistedPaths))
            ->ignoreDotFiles(true)
            ->ignoreUnreadableDirs()
            ->ignoreVCSIgnored(true)
            ->in($parameters['directory'])
            ->sortByName()
            ->getIterator());

        $issues = [];

        foreach ($filesOrDirectories as $fileOrDirectory) {
            $name = SpellcheckFormatter::format($fileOrDirectory->getFilenameWithoutExtension());

            $newIssues = array_map(
                fn (Misspelling $misspelling): Issue => new Issue(
                    $misspelling,
                    $fileOrDirectory->getRealPath(),
                    0,
                ), $this->spellchecker->check($name),
            );

            $issues = [
                ...$issues,
                ...$newIssues,
            ];

            $newIssues !== [] ? $parameters['onFailure']() : $parameters['onSuccess']();
        }

        return $issues;
    }
}

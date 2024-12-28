<?php

declare(strict_types=1);

namespace Peck\Checkers;

use Peck\Config;
use Peck\Contracts\Checker;
use Peck\Contracts\Services\Spellchecker;
use Peck\ValueObjects\Issue;
use Peck\ValueObjects\Misspelling;
use Symfony\Component\Finder\Finder;

/**
 * @internal
 */
readonly class FileSystemChecker implements Checker
{
    /**
     * Creates a new instance of FsChecker.
     */
    public function __construct(
        private Config $config,
        private Spellchecker $spellchecker,
    ) {
        //
    }

    /**
     * Checks for issues in the given directory.
     *
     * @param  array<string, string>  $parameters
     * @return array<int, Issue>
     */
    public function check(array $parameters): array
    {
        $filesOrDirectories = Finder::create()
            ->notPath($this->config->whitelistedDirectories)
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
            ->ignoreUnreadableDirs()
            ->ignoreVCSIgnored(true)
            ->in($parameters['directory'])
            ->getIterator();

        $issues = [];

        foreach ($filesOrDirectories as $fileOrDirectory) {
            $name = $fileOrDirectory->getFilenameWithoutExtension();
            $name = strtolower((string) preg_replace('/(?<!^)[A-Z]/', ' $0', $name));

            $issues = [
                ...$issues,
                ...array_map(
                    fn (Misspelling $misspelling): Issue => new Issue(
                        $misspelling,
                        $fileOrDirectory->getRealPath(),
                        0,
                    ), $this->spellchecker->check($name)),
            ];
        }

        usort($issues, fn (Issue $a, Issue $b): int => $a->file <=> $b->file);

        return array_values($issues);
    }
}

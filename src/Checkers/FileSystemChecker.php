<?php

declare(strict_types=1);

namespace Peck\Checkers;

use Peck\Contracts\Checker;
use Peck\Contracts\Services\Spellchecker;
use Peck\Support\NameParser;
use Peck\ValueObjects\Issue;
use Peck\ValueObjects\Misspelling;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal
 */
final readonly class FileSystemChecker implements Checker
{
    /**
     * Creates a new instance of FileSystemChecker.
     */
    public function __construct(
        private Spellchecker $spellchecker,
    ) {}

    /**
     * Checks the given file for issues.
     *
     * @return array<int, Issue>
     */
    public function check(SplFileInfo $file): array
    {
        $name = NameParser::parse($file->getFilenameWithoutExtension());

        $issues = array_map(
            fn (Misspelling $misspelling): Issue => new Issue(
                $misspelling,
                $file->getRealPath(),
                0,
            ),
            $this->spellchecker->check($name)
        );

        usort($issues, fn (Issue $a, Issue $b): int => $a->file <=> $b->file);

        return array_values($issues);
    }

    /**
     * Checks if the checker supports the given file.
     */
    public function supports(SplFileInfo $file): bool
    {
        if ($file->isDir()) {
            return true;
        }

        return $file->isFile() && $file->getExtension() === 'php';
    }
}

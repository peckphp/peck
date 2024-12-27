<?php

declare(strict_types=1);

namespace Peck\Checkers;

use Iterator;
use Peck\Config;
use Peck\Contracts\Checker;
use Peck\Contracts\Services\Spellchecker;
use Peck\ValueObjects\Issue;
use Peck\ValueObjects\Misspelling;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

/**
 * @internal
 */
readonly class MethodNameChecker implements Checker
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
        $files = $this->getPhpFiles($parameters['directory']);

        $issues = [];

        foreach ($files as $file) {
            $methods = $this->getMethodDeclarations($file);

            foreach ($methods as $method) {
                $issues = [
                    ...$issues,
                    ...array_map(
                        fn (Misspelling $misspelling): Issue => new Issue(
                            $misspelling,
                            $file->getRealPath(),
                            $method['line'],
                        ),
                        $this->spellchecker->check($method['name'])
                    ),
                ];
            }
        }

        return $issues;
    }

    /**
     * Retrieves all method declarations in the given file.
     * Uses a simple regex to find method declarations.
     *
     * @return array{name: string, line: int}[]
     */
    private function getMethodDeclarations(SplFileInfo $file): array
    {
        $content = file_get_contents($file->getRealPath());

        if ($content === false) {
            return [];
        }

        $matches = [];
        \preg_match_all('/function\s+([a-zA-Z0-9_]+)\s*\(/m', $content, $matches, PREG_OFFSET_CAPTURE);

        return array_map(function (array $match) use ($content): array {
            $method = \ltrim($match[0], '__');

            return [
                'name' => $this->prepareMethodName($method),
                'line' => substr_count(substr($content, 0, $match[1]), "\n") + 1,
            ];
        }, $matches[1]);
    }

    /**
     * Prepares the method name for spellchecking.
     * e.g. ‘getHTTPClient‘ -> 'get http client'
     * e.g. '__construct' -> 'construct'
     */
    private function prepareMethodName(string $methodName): string
    {
        return strtolower(
            (string) \preg_replace(
                '/(?<!^)[A-Z]/',
                ' $0',
                (string) \preg_replace('/[^a-zA-Z0-9]/', '', $methodName)
            )
        );
    }

    /**
     * Fetches all PHP files in the given directory.
     *
     * @return Iterator<string, SplFileInfo>
     */
    private function getPhpFiles(string $directory): Iterator
    {
        return Finder::create()
            ->notPath($this->config->whitelistedDirectories)
            ->ignoreUnreadableDirs()
            ->in($directory)
            ->name('*.php')
            ->getIterator();
    }
}

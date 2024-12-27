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
readonly class ClassFunctionNameChecker implements Checker
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
        $phpFiles = Finder::create()
            ->files()
            ->name('*.php')
            ->notPath($this->config->whitelistedDirectories)
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
            ->ignoreUnreadableDirs()
            ->ignoreVCSIgnored(true)
            ->in($parameters['directory']);

        $issues = [];

        foreach ($phpFiles as $file) {

            $words = $this->getFunctionsAndClasses($file->getRealPath());

            foreach ($words as $word) {
                $issues = [
                    ...$issues,
                    ...array_map(
                        fn (Misspelling $misspelling): Issue => new Issue(
                            $misspelling,
                            $file->getRealPath(),
                            $word['line'],
                        ), $this->spellchecker->check($word['name'])),
                ];
            }
        }

        usort($issues, fn (Issue $a, Issue $b): int => $a->file <=> $b->file);

        return array_values($issues);
    }

    /**
     * Get all classe names and function namee from a file.
     *
     * @return array<int, array{
     *     name: string,
     *     line: int
     * }>
     */
    private function getFunctionsAndClasses(string $filePath): array
    {
        $code = (string) file_get_contents($filePath);
        $tokens = token_get_all($code);

        $names = [];

        foreach ($tokens as $index => $token) {
            if (is_array($token)) {
                [$id, , $line] = $token;

                // Detect classes
                if ($id === T_CLASS && isset($tokens[$index + 2]) && is_array($tokens[$index + 2])) {
                    $className = $tokens[$index + 2][1];
                    $className = strtolower((string) preg_replace('/(?<!^)[A-Z]/', ' $0', $className));

                    $names[] = [
                        'name' => $className,
                        'line' => $line,
                    ];
                }

                // Detect functions
                if ($id === T_FUNCTION && isset($tokens[$index + 2]) && is_array($tokens[$index + 2])) {
                    $functionName = $tokens[$index + 2][1];
                    $functionName = strtolower((string) preg_replace('/(?<!^)[A-Z]/', ' $0', $functionName));
                    $names[] = [
                        'name' => $functionName,
                        'line' => $line,
                    ];
                }
            }
        }

        return $names;
    }
}

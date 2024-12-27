<?php

declare(strict_types=1);

namespace Peck\Checkers;

use Composer\Autoload\ClassLoader;
use Peck\Contracts\Checker;
use Peck\Contracts\Services\Spellchecker;
use Peck\ValueObjects\Issue;
use Peck\ValueObjects\Misspelling;
use Symfony\Component\Finder\Finder;

/**
 * @internal
 */
readonly class ClassMethodNameChecker implements Checker
{
    /**
     * Creates a new instance of ClassMethodNameChecker.
     */
    public function __construct(
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
        $files = Finder::create()
            ->files()
            ->name('*.php')
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
            ->ignoreUnreadableDirs()
            ->ignoreVCSIgnored(true)
            ->in($parameters['directory'])
            ->getIterator();

        $issues = [];

        $registeredLoaders = array_values(ClassLoader::getRegisteredLoaders())[0];
        $projectPath = realpath($parameters['directory']) ?: '';
        $namespacePrefix = $namespacePath = '';

        foreach ($registeredLoaders->getPrefixesPsr4() as $key => $value) {
            $realPath = realpath($value[0]) ?: '';

            if (! str_starts_with($projectPath, $realPath)) {
                continue;
            }

            $namespacePrefix = $key;
            $namespacePath = realpath($value[0]);
            break;
        }

        foreach ($files as $file) {
            $fileDirname = pathinfo($file->getRealPath(), PATHINFO_DIRNAME);

            $relativePath = substr($fileDirname, strlen((string) $namespacePath));
            $relativePath = ltrim(str_replace('/', '\\', $relativePath), '\\');

            $filePath = $namespacePrefix
                .$relativePath
                .'\\'.$file->getFilenameWithoutExtension();

            if (! class_exists($filePath)) {
                continue;
            }

            $object = new \ReflectionClass($filePath);

            foreach ($object->getMethods() as $method) {
                $name = strtolower((string) preg_replace('/(?<!^)[A-Z]/', ' $0', $method->getName()));

                $issues = [
                    ...$issues,
                    ...array_map(
                        fn (Misspelling $misspelling): Issue => new Issue(
                            $misspelling,
                            $file->getRealPath(),
                            0,
                        ),
                        $this->spellchecker->check($name),
                    ),
                ];
            }
        }

        return $issues;
    }
}

<?php

declare(strict_types=1);

namespace Peck\Checkers;

use Peck\Config;
use Peck\Contracts\Checker;
use Peck\Contracts\Services\Spellchecker;
use Peck\Support\NameParser;
use Peck\ValueObjects\Issue;
use Peck\ValueObjects\Misspelling;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal
 */
final readonly class ClassChecker implements Checker
{
    /**
     * Creates a new instance of ClassChecker.
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
        $classesFiles = Finder::create()
            ->files()
            ->notPath($this->config->whitelistedDirectories)
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
            ->ignoreUnreadableDirs()
            ->ignoreVCSIgnored(true)
            ->in($parameters['directory'])
            ->name('*.php')
            ->getIterator();

        $issues = [];

        foreach ($classesFiles as $classFile) {
            $issues = [
                ...$issues,
                ...$this->getIssuesFromClass($classFile),
            ];
        }

        usort($issues, fn (Issue $a, Issue $b): int => $a->file <=> $b->file);

        return array_values($issues);
    }

    /**
     * Get the issues from the given class.
     *
     * @return array<int, Issue>
     */
    public function getIssuesFromClass(SplFileInfo $file): array
    {
        $class = $this->getClassNameWithNamespace($file);

        if ($class === null) {
            return [];
        }

        $reflectionClass = new ReflectionClass($class);

        $namesToCheck = [
            ...$this->getMethodNames($reflectionClass),
            ...$this->getPropertyNames($reflectionClass),
            ...$this->getConstantNames($reflectionClass),
        ];

        if ($docComment = $reflectionClass->getDocComment()) {
            $namesToCheck = [
                ...$namesToCheck,
                ...explode(PHP_EOL, $docComment),
            ];
        }

        if ($namesToCheck === []) {
            return [];
        }

        $issues = [];

        foreach ($namesToCheck as $name) {
            $issues = [
                ...$issues,
                ...array_map(
                    fn (Misspelling $misspelling): Issue => new Issue(
                        $misspelling,
                        $file->getRealPath(),
                        $this->getErrorLine($file, $name),
                    ), $this->spellchecker->check(NameParser::parse($name))),
            ];
        }

        return $issues;
    }

    /**
     * Get the method names contained in the given class.
     *
     * @param  ReflectionClass<object>  $class
     * @return array<int, string>
     */
    private function getMethodNames(ReflectionClass $class): array
    {
        foreach ($class->getMethods() as $method) {
            if ($method->class !== $class->name) {
                continue;
            }
            $namesToCheck[] = $method->getName();
            $namesToCheck = [
                ...$namesToCheck,
                ...$this->getMethodParameters($method),
            ];

            if ($docComment = $method->getDocComment()) {
                $namesToCheck = [
                    ...$namesToCheck,
                    ...explode(PHP_EOL, $docComment),
                ];
            }
        }

        return $namesToCheck ?? [];
    }

    /**
     * Get the method parameters names contained in the given method.
     *
     * @return array<int, string>
     */
    private function getMethodParameters(ReflectionMethod $method): array
    {
        return array_map(
            fn (ReflectionParameter $parameter): string => $parameter->getName(),
            $method->getParameters(),
        );
    }

    /**
     * Get the constant names and their values contained in the given class.
     *
     * @param  ReflectionClass<object>  $class
     * @return array<int, string>
     */
    private function getConstantNames(ReflectionClass $class): array
    {
        return array_merge(...array_values(array_map(
            function (ReflectionClassConstant $constant) use ($class): array {
                if ($constant->class !== $class->name) {
                    return [];
                }

                return is_string($constant->getValue())
                    ? [$constant->name, $constant->getValue()]
                    : [$constant->name];
            },
            $class->getReflectionConstants()
        )));
    }

    /**
     * Get the property names contained in the given class.
     *
     * @param  ReflectionClass<object>  $class
     * @return array<int, string>
     */
    private function getPropertyNames(ReflectionClass $class): array
    {
        $properties = $class->getProperties();
        $propertiesNames = array_map(
            fn (ReflectionProperty $property): string => $property->getName(),
            array_filter(
                $properties,
                fn (ReflectionProperty $property): bool => $property->class === $class->name,
            )
        );

        $propertiesDocComments = array_reduce(
            array_map(
                fn (ReflectionProperty $property): array => explode(PHP_EOL, $property->getDocComment() ?: ''),
                $properties,
            ),
            fn (array $carry, array $item): array => [
                ...$carry,
                ...$item,
            ],
            [],
        );

        return [
            ...$propertiesNames,
            ...$propertiesDocComments,
        ];
    }

    /**
     * Get the full class name with namespace.
     *
     * @return class-string|null
     */
    private function getClassNameWithNamespace(SplFileInfo $file): ?string
    {
        if (preg_match('/namespace (.*);/', $file->getContents(), $matches)) {
            /**
             * @var class-string
             */
            $class = $matches[1].'\\'.$file->getFilenameWithoutExtension();

            return $class;
        }

        return null;
    }

    /**
     * Get the line number of the error.
     */
    private function getErrorLine(SplFileInfo $file, string $misspellingWord): int
    {
        $contentsArray = explode(PHP_EOL, $file->getContents());
        $contentsArrayLines = array_map(fn ($lineNumber): int => $lineNumber + 1, array_keys($contentsArray));

        $lines = array_values(array_filter(
            array_map(
                fn (string $line, int $lineNumber): ?int => str_contains($line, $misspellingWord) ? $lineNumber : null,
                $contentsArray,
                $contentsArrayLines,
            ),
        ));

        if ($lines === []) {
            return 0;
        }

        return $lines[0];
    }
}

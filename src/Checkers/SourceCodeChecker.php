<?php

declare(strict_types=1);

namespace Peck\Checkers;

use BackedEnum;
use Peck\Config;
use Peck\Contracts\Checker;
use Peck\Contracts\Services\Spellchecker;
use Peck\Support\SpellcheckFormatter;
use Peck\ValueObjects\Issue;
use Peck\ValueObjects\Misspelling;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal
 */
final readonly class SourceCodeChecker implements Checker
{
    /**
     * Creates a new instance of SourceCodeChecker.
     */
    public function __construct(
        private Config $config,
        private Spellchecker $spellchecker,
    ) {}

    /**
     * Checks for issues in the given directory.
     *
     * @param  array<string, string>  $parameters
     * @return array<int, Issue>
     */
    public function check(array $parameters): array
    {
        $sourceFiles = Finder::create()
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

        foreach ($sourceFiles as $sourceFile) {
            $issues = [
                ...$issues,
                ...$this->getIssuesFromSourceFile($sourceFile),
            ];
        }

        usort($issues, fn (Issue $a, Issue $b): int => $a->file <=> $b->file);

        return array_values($issues);
    }

    /**
     * Get the issues from the given source file.
     *
     * @return array<int, Issue>
     */
    private function getIssuesFromSourceFile(SplFileInfo $file): array
    {
        $definition = $this->getFullyQualifiedDefinitionName($file);

        if ($definition === null) {
            return [];
        }

        $reflection = new ReflectionClass($definition);

        $namesToCheck = [
            ...$this->getMethodNames($reflection),
            ...$this->getPropertyNames($reflection),
            ...$this->getConstantNames($reflection),
        ];

        if ($docComment = $reflection->getDocComment()) {
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
                    ), $this->spellchecker->check(SpellcheckFormatter::format($name))),
            ];
        }

        return $issues;
    }

    /**
     * Get the method names contained in the given reflection.
     *
     * @param  ReflectionClass<object>  $reflection
     * @return array<int, string>
     */
    private function getMethodNames(ReflectionClass $reflection): array
    {
        foreach ($reflection->getMethods() as $method) {
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
     * Get the constant names and their values contained in the given reflection.
     * This also includes cases from enums and their values (for string backed enums).
     *
     * @param  ReflectionClass<object>  $reflection
     * @return array<int, string>
     */
    private function getConstantNames(ReflectionClass $reflection): array
    {
        $constants = $reflection->getConstants();

        return array_values(array_filter([
            ...array_keys($constants),
            ...array_map(fn (mixed $value): mixed => $value instanceof BackedEnum && is_string($value->value) ? $value->value : $value, array_values($constants)),
        ], fn (mixed $values): bool => is_string($values)));
    }

    /**
     * Get the property names contained in the given reflection.
     *
     * @param  ReflectionClass<object>  $reflection
     * @return array<int, string>
     */
    private function getPropertyNames(ReflectionClass $reflection): array
    {
        $properties = $reflection->getProperties();

        $propertiesNames = array_map(
            fn (ReflectionProperty $property): string => $property->getName(),
            $properties,
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
     * Get the fully qualified definition name of the class, enum or trait.
     *
     * @return class-string<object>|null
     */
    private function getFullyQualifiedDefinitionName(SplFileInfo $file): ?string
    {
        if (preg_match('/namespace (.*);/', $file->getContents(), $matches)) {
            /** @var class-string */
            $fullyQualifiedName = $matches[1].'\\'.$file->getFilenameWithoutExtension();

            return $fullyQualifiedName;
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

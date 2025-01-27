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
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;
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
     * @param  array{directory: string, onSuccess: callable(): void, onFailure: callable(): void}  $parameters
     * @return array<int, Issue>
     */
    public function check(array $parameters): array
    {
        $sourceFiles = iterator_to_array(Finder::create()
            ->files()
            ->notPath(NotPaths::get($parameters['directory'], $this->config->whitelistedPaths))
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
            ->ignoreUnreadableDirs()
            ->ignoreVCSIgnored(true)
            ->in($parameters['directory'])
            ->name('*.php')
            ->sortByName()
            ->getIterator());

        $issues = [];

        foreach ($sourceFiles as $sourceFile) {
            $newIssues = $this->getIssuesFromSourceFile($sourceFile);

            $issues = [
                ...$issues,
                ...$newIssues,
            ];

            $newIssues !== [] ? $parameters['onFailure']() : $parameters['onSuccess']();
        }

        return $issues;
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

        try {
            $reflection = new ReflectionClass($definition);
        } catch (ReflectionException) { // @phpstan-ignore-line
            return [];
        }

        $namesToCheck = [
            ...$this->getMethodNames($reflection),
            ...$this->getPropertyNames($reflection),
            ...$this->getConstantNames($reflection),
        ];

        if ($docComment = $reflection->getDocComment()) {
            $namesToCheck = [
                ...$namesToCheck,
                ...array_values(array_filter(
                    explode(PHP_EOL, $docComment),
                    fn (string $line): bool => ! str_contains($line, '* @',
                    ))),
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
        $methods = array_filter(
            $reflection->getMethods(),
            function (ReflectionMethod $method) use ($reflection): bool {
                foreach ($reflection->getTraits() as $trait) {
                    if ($trait->hasMethod($method->getName())) {
                        return false;
                    }
                }

                return $method->class === $reflection->name;
            },
        );

        foreach ($methods as $method) {
            $namesToCheck[] = $method->getName();
            $namesToCheck = [
                ...$namesToCheck,
                ...$this->getMethodParameters($method),
            ];

            if ($docComment = $method->getDocComment()) {
                $namesToCheck = [
                    ...$namesToCheck,
                    ...array_values(array_filter(
                        explode(PHP_EOL, $docComment),
                        fn (string $line): bool => ! str_contains($line, '* @',
                        ))),
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
        return array_map(
            fn (ReflectionClassConstant $constant): string => $constant->name,
            array_values(array_filter(
                $reflection->getReflectionConstants(),
                function (ReflectionClassConstant $constant) use ($reflection): bool {
                    if ($constant->class !== $reflection->name) {
                        return false;
                    }

                    foreach ($reflection->getTraits() as $trait) {
                        if ($trait->hasConstant($constant->getName())) {
                            return false;
                        }
                    }

                    return true;
                }
            ))
        );
    }

    /**
     * Get the property names contained in the given reflection.
     *
     * @param  ReflectionClass<object>  $reflection
     * @return array<int, string>
     */
    private function getPropertyNames(ReflectionClass $reflection): array
    {
        $properties = array_filter(
            $reflection->getProperties(),
            function (ReflectionProperty $property) use ($reflection): bool {
                foreach ($reflection->getTraits() as $trait) {
                    if ($trait->hasProperty($property->getName())) {
                        return false;
                    }
                }

                return $property->class === $reflection->name;
            },
        );

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
            /** @var class-string $fullyQualifiedName */
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

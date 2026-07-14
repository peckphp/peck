<?php

declare(strict_types=1);

namespace Peck;

use Closure;
use Peck\Support\PresetProvider;
use Peck\Support\ProjectPath;

final class Config
{
    /**
     * The name of the configuration file.
     */
    private const JSON_CONFIGURATION_NAME = 'peck.json';

    /**
     * The default language passed to Aspell.
     */
    private const DEFAULT_LANGUAGE = 'en_US';

    /**
     * The instance of the configuration.
     */
    private static ?self $instance = null;

    /**
     * The closure to resolve the config file path.
     */
    private static ?Closure $resolveConfigFilePathUsing = null;

    /**
     * Creates a new instance of Config.
     *
     * @param  array<int, string>  $whitelistedWords
     * @param  array<int, string>  $whitelistedPaths
     * @param  array<string,  array<int, string>>  $fileSpecificIgnores
     */
    public function __construct(
        public array $whitelistedWords = [],
        public array $whitelistedPaths = [],
        public array $fileSpecificIgnores = [],
        public ?string $preset = null,
        public ?string $language = null,
    ) {
        $this->whitelistedWords = array_map(strtolower(...), $whitelistedWords);
        $this->fileSpecificIgnores = array_map(
            fn (array $words): array => array_map(strtolower(...), $words),
            $fileSpecificIgnores
        );
    }

    /**
     * Resolves the configuration file path.
     */
    public static function resolveConfigFilePathUsing(Closure $closure): void
    {
        self::flush();

        self::$resolveConfigFilePathUsing = $closure;
    }

    /**
     * Flushes the configuration.
     */
    public static function flush(): void
    {
        self::$instance = null;
        self::$resolveConfigFilePathUsing = null;
    }

    /**
     * Checks if the configuration file exists.
     */
    public static function exists(): bool
    {
        return file_exists(ProjectPath::get().'/'.self::JSON_CONFIGURATION_NAME);
    }

    /**
     * Fetches the instance of the configuration.
     */
    public static function instance(): self
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        $basePath = ProjectPath::get();
        $filePath = $basePath.'/'.(self::$resolveConfigFilePathUsing instanceof Closure
                ? (self::$resolveConfigFilePathUsing)()
                : self::JSON_CONFIGURATION_NAME);

        $contents = file_exists($filePath)
            ? (string) file_get_contents($filePath)
            : '{}';

        /**
         * @var array{
         *     preset?: string,
         *     language?: string,
         *     ignore?: array{
         *         words?: array<int, string>,
         *         paths?: array<int, string>,
         *         files?: array<string, array<int, string>>
         *     }
         *  } $jsonAsArray
         */
        $jsonAsArray = json_decode($contents, true) ?: [];

        return self::$instance = new self(
            $jsonAsArray['ignore']['words'] ?? [],
            $jsonAsArray['ignore']['paths'] ?? [],
            $jsonAsArray['ignore']['files'] ?? [],
            $jsonAsArray['preset'] ?? null,
            $jsonAsArray['language'] ?? null,
        );
    }

    /**
     * Creates the configuration file for the user running the command.
     */
    public static function init(): bool
    {
        $filePath = ProjectPath::get().'/'.self::JSON_CONFIGURATION_NAME;

        if (file_exists($filePath)) {
            return false;
        }

        return (bool) file_put_contents($filePath, json_encode([
            ...match (true) {
                class_exists('\Illuminate\Support\Str') => [
                    'preset' => 'laravel',
                ],
                default => [
                    'preset' => 'base',
                ],
            },
            'ignore' => [
                'words' => [
                    'php',
                ],
                'paths' => [],
            ],
        ], JSON_PRETTY_PRINT));
    }

    /**
     * Adds a word to the ignore list.
     *
     * @param  array<int, string>  $words
     */
    public function ignoreWords(array $words): void
    {
        $this->whitelistedWords = array_merge($this->whitelistedWords, array_map(strtolower(...), $words));

        $this->persist();
    }

    /**
     * Checks if the word is ignored globally or for a specific file.
     */
    public function isWordIgnored(string $word, ?string $filePath = null): bool
    {
        $word = strtolower($word);

        // Check global ignores
        $globalIgnores = [
            ...$this->whitelistedWords,
            ...array_map(strtolower(...), PresetProvider::whitelistedWords($this->preset)),
        ];

        if (in_array($word, $globalIgnores)) {
            return true;
        }

        // Check file-specific ignores
        if ($filePath !== null) {
            $projectPath = ProjectPath::get();

            // Normalize the file path to be relative to project root
            $normalizedFilePath = $filePath;
            if (str_starts_with($filePath, $projectPath.'/')) {
                $normalizedFilePath = substr($filePath, strlen($projectPath) + 1);
            }

            foreach ($this->fileSpecificIgnores as $path => $words) {
                if ($normalizedFilePath === $path && in_array($word, $words)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Retrieves the configured language or the default
     */
    public function getLanguage(): string
    {
        return $this->language ?? self::DEFAULT_LANGUAGE;
    }

    /**
     * Save the configuration to the file.
     */
    private function persist(): void
    {
        $filePath = ProjectPath::get().'/'.self::JSON_CONFIGURATION_NAME;

        file_put_contents($filePath, json_encode([
            ...$this->preset !== null ? ['preset' => $this->preset] : [],
            ...$this->language !== null ? ['language' => $this->language] : [],
            'ignore' => [
                'words' => $this->whitelistedWords,
                'paths' => $this->whitelistedPaths,
                'files' => $this->fileSpecificIgnores,
            ],
        ], JSON_PRETTY_PRINT));
    }
}

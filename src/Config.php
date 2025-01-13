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
    private const string JSON_CONFIGURATION_NAME = 'peck.json';

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
     */
    public function __construct(
        public array $whitelistedWords = [],
        public array $whitelistedPaths = [],
        public ?string $preset = null,
    ) {
        $this->whitelistedWords = array_map(strtolower(...), $whitelistedWords);
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
         *     ignore?: array{
         *         words?: array<int, string>,
         *         paths?: array<int, string>
         *     }
         *  } $jsonAsArray
         */
        $jsonAsArray = json_decode($contents, true) ?: [];

        return self::$instance = new self(
            $jsonAsArray['ignore']['words'] ?? [],
            $jsonAsArray['ignore']['paths'] ?? [],
            $jsonAsArray['preset'] ?? null,
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
     * Checks if the word is ignored.
     */
    public function isWordIgnored(string $word): bool
    {
        return in_array(strtolower($word), [
            ...$this->whitelistedWords,
            ...PresetProvider::whitelistedWords($this->preset),
        ]);
    }

    /**
     * Save the configuration to the file.
     */
    private function persist(): void
    {
        $filePath = ProjectPath::get().'/'.self::JSON_CONFIGURATION_NAME;

        file_put_contents($filePath, json_encode([
            ...$this->preset !== null ? ['preset' => $this->preset] : [],
            'ignore' => [
                'words' => $this->whitelistedWords,
                'paths' => $this->whitelistedPaths,
            ],
        ], JSON_PRETTY_PRINT));
    }
}

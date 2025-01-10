<?php

declare(strict_types=1);

namespace Peck;

use Closure;
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
     * @param  array<int, string>  $whitelistedDirectories
     */
    public function __construct(
        public array $whitelistedWords = [],
        public array $whitelistedDirectories = [],
        public ?string $language = null,
    ) {
        $this->whitelistedWords = array_map(fn (string $word): string => strtolower($word), $whitelistedWords);
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
         *     language?: string,
         *     ignore?: array{
         *         words?: array<int, string>,
         *         directories?: array<int, string>
         *     }
         *  } $jsonAsArray
         */
        $jsonAsArray = json_decode($contents, true) ?: [];

        return self::$instance = new self(
            $jsonAsArray['ignore']['words'] ?? [],
            $jsonAsArray['ignore']['directories'] ?? [],
            $jsonAsArray['language'] ?? null,
        );
    }

    /**
     * Creates the configuration file for the user running the command.
     */
    public static function init(): bool
    {
        $filePath = ProjectPath::get().'/'.self::JSON_CONFIGURATION_NAME;

        return ! file_exists($filePath) && file_put_contents($filePath, json_encode([
            'ignore' => [
                'words' => [
                    'php',
                ],
                'directories' => [],
            ],
        ], JSON_PRETTY_PRINT));
    }
}

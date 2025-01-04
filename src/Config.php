<?php

declare(strict_types=1);

namespace Peck;

use Closure;
use Composer\Autoload\ClassLoader;

final class Config
{
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

        $basePath = dirname(array_keys(ClassLoader::getRegisteredLoaders())[0]);
        $filePath = $basePath.'/'.(self::$resolveConfigFilePathUsing instanceof Closure
            ? (self::$resolveConfigFilePathUsing)()
            : 'peck.json');

        $contents = file_exists($filePath)
            ? (string) file_get_contents($filePath)
            : '{}';

        /** @var array{
         *     ignore?: array{
         *         words?: array<int, string>,
         *         directories?: array<int, string>
         *     }
         *  } $jsonAsArray */
        $jsonAsArray = json_decode($contents, true) ?: [];

        return self::$instance = new self(
            $jsonAsArray['ignore']['words'] ?? [],
            $jsonAsArray['ignore']['directories'] ?? [],
        );
    }
}

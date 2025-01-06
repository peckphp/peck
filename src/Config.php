<?php

declare(strict_types=1);

namespace Peck;

use Composer\Autoload\ClassLoader;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

final class Config
{
    /**
     * The instance of the configuration.
     */
    private static ?self $instance = null;

    /**
     * The name of the configuration file
     */
    private const string CONFIG_FILE_NAME = 'peck.json';

    /**
     * The maximum depth to search for the configuration file.
     */
    private const int CONFIG_SEARCH_MAX_DEPTH = 3;

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
     * Fetches the instance of the configuration.
     */
    public static function instance(?InputInterface $input = null): self
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        $defaultConfigPath = dirname(array_keys(ClassLoader::getRegisteredLoaders())[0]) . '/';
        $configFile = self::findConfigFile($input, defaultConfigPath:  $defaultConfigPath);

        if ($configFile === null) {
            throw new \RuntimeException('Configuration file "peck.json" not found.');
        }

        $contents = (string) file_get_contents($configFile);

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

    /**
     * Find the configuration file.
     *
     * @param InputInterface|null $customConfigPath
     * @param string $defaultConfigPath
     * @return string|null The path to the configuration file, or null if not found.
     */
    private static function findConfigFile( ?InputInterface $input, string $defaultConfigPath): ?string
    {
        // Get the custom config path from the input
        $customConfigPath = $input?->getOption('config-path');

        // Debugging: Check the value of $customConfigPath
        if ($customConfigPath) {
            echo "Custom config path provided: " . $customConfigPath . PHP_EOL; // Debugging output
        } else {
            echo "No custom config path provided, using default." . PHP_EOL; // Debugging output
        }

        // If a custom config path is provided, check if it's a valid file
        if ($customConfigPath) {
            $configPath = rtrim($customConfigPath, '/') . '/' . self::CONFIG_FILE_NAME;
            echo "Checking config file at: " . $configPath . PHP_EOL; // Debugging output
            if (is_file($configPath)) {
                return $configPath;
            }
        }

        // Fallback to the default config path
        $configPath = $defaultConfigPath . self::CONFIG_FILE_NAME;
        echo "Fallback config path: " . $configPath . PHP_EOL; // Debugging output

        if (is_file($configPath)) {
            return $configPath;
        }
////        $configPath = isset($customConfigPath) ? $defaultConfigPath . $customConfigPath?->getOption('config-path') : $defaultConfigPath;
//
//        $configPath = $customConfigPath?->getOption('config-path') ?: $defaultConfigPath;
//
//        if (is_file($configPath) && basename($configPath) === self::CONFIG_FILE_NAME) {
//            return $configPath;
//        }

        if (is_dir(dirname($configPath))) {

            $finder = new Finder();
            $finder->files()->name(self::CONFIG_FILE_NAME)->in($configPath)->depth('< ' . self::CONFIG_SEARCH_MAX_DEPTH);

            foreach ($finder as $file) {
                return $file->getRealPath();
            }
        }

        return null;
    }
}

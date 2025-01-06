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
        $configFile = self::findConfigFile(inputOptions:  $input, defaultConfigPath:  $defaultConfigPath);

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
     * Finds the configuration file based on the provided input and default path.
     *
     * @param InputInterface|null $inputOptions
     * @param string $defaultConfigPath The default path to the configuration file.
     * @return string|null The path to the configuration file, or null if not found.
     */
    private static function findConfigFile(?InputInterface $inputOptions, string $defaultConfigPath): ?string
    {
        $customConfigPath = self::getCustomConfigPath($inputOptions);
        if ($customConfigPath && self::isValidConfigFile($customConfigPath)) {
            return $customConfigPath;
        }

        $defaultConfigFilePath = self::getDefaultConfigPath($defaultConfigPath);
        if (self::isValidConfigFile($defaultConfigFilePath)) {
            return $defaultConfigFilePath;
        }

        return self::searchForConfigFileInDirectory(dirname($defaultConfigFilePath));
    }

    /**
     * Retrieves the custom configuration path from the input.
     *
     * @param InputInterface|null $inputOptions
     * @return string|null The custom configuration path, or null if not provided.
     */
    private static function getCustomConfigPath(?InputInterface $inputOptions): ?string
    {
        $customConfigPath = $inputOptions?->getOption('config-path');
        return $customConfigPath ? rtrim($customConfigPath, '/') . '/' . self::CONFIG_FILE_NAME : null;
    }

    /**
     * Constructs the default configuration path.
     *
     * @param string $defaultConfigPath The base path for the configuration file.
     * @return string The full path to the default configuration file.
     */
    private static function getDefaultConfigPath(string $defaultConfigPath): string
    {
        return $defaultConfigPath . self::CONFIG_FILE_NAME;
    }

    /**
     * Validates if the given file path is a valid configuration file.
     *
     * @param string $filePath The path to the configuration file.
     * @return bool True if the file is valid, false otherwise.
     */
    private static function isValidConfigFile(string $filePath): bool
    {
        return is_file($filePath) && basename($filePath) === self::CONFIG_FILE_NAME;
    }

    /**
     * Searches for the configuration file in the specified directory.
     *
     * @param string $directory The directory to search for the configuration file.
     * @return string|null The path to the found configuration file, or null if not found.
     */
    private static function searchForConfigFileInDirectory(string $directory): ?string
    {
        if (is_dir($directory)) {
            $finder = new Finder();
            $finder->files()->name(self::CONFIG_FILE_NAME)->in($directory)->depth('< ' . self::CONFIG_SEARCH_MAX_DEPTH);

            foreach ($finder as $file) {
                return $file->getRealPath();
            }
        }
        return null;
    }
}

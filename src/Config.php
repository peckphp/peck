<?php

declare(strict_types=1);

namespace Peck;

use Composer\Autoload\ClassLoader;
use InvalidArgumentException;

final class Config
{
    /**
     * The instance of the configuration.
     */
    private static ?self $instance = null;

    /**
     * Creates a new instance of Config.
     *
     * @param  array<int, string>  $whitelistedWords
     * @param  array<int, string>  $whitelistedDirectories
     */
    public function __construct(
        public array $whitelistedWords = [],
        public array $whitelistedDirectories = [],
        public ?string $configFilePath = null,
    ) {
        $this->whitelistedWords = array_map(fn (string $word): string => strtolower($word), $whitelistedWords);
    }

    /**
     * Fetches the instance of the configuration.
     */
    public static function instance(bool $refresh = false, ?string $configFilePath = null): self
    {
        if (self::$instance instanceof self && ! $refresh) {
            return self::$instance;
        }

        $config = (new self)->setConfigFilepath($configFilePath)->getConfigAsArray();

        return self::$instance = new self(
            whitelistedWords: $config['ignore']['words'] ?? [],
            whitelistedDirectories: $config['ignore']['directories'] ?? [],
            configFilePath: $configFilePath,
        );
    }

    /**
     * Returns the config filepath.
     */
    public function getConfigFilePath(): string
    {
        if (! is_null($this->configFilePath) && file_exists($this->configFilePath)) {
            return $this->configFilePath;
        }

        $basePath = dirname(array_keys(ClassLoader::getRegisteredLoaders())[0]);

        return sprintf('%s/peck.json', $basePath);
    }

    /**
     * Returns the config as an array
     *
     * @return array{
     *     ignore?: array{
     *         words?: array<int, string>,
     *         directories?: array<int, string>
     *     }
     * }
     */
    public function getConfigAsArray(): array
    {
        $contents = (string) file_get_contents($this->getConfigFilePath());
        $config = json_decode($contents, true);

        if (! is_array($config)) {
            return [];
        }

        return $config;
    }

    /*
     * Set config values using dot notation for nested array values
     *
     * @phpstan-ignore-next-line
     */
    public function set(string $dotNotationKey, array $value): void
    {
        $config = $this->getConfigAsArray();

        $keys = explode('.', $dotNotationKey);

        $referencedKeyValue = &$config;
        foreach ($keys as $key) {
            if (! isset($referencedKeyValue[$key])) {
                throw new InvalidArgumentException("Cannot set the config value for '{$dotNotationKey}'. The key '{$key}' does not exist in the array.");
            }
            /** @phpstan-ignore-next-line */
            $referencedKeyValue = &$referencedKeyValue[$key];
        }

        $referencedKeyValue = $value;

        file_put_contents($this->getConfigFilePath(), json_encode($config, JSON_PRETTY_PRINT));
    }

    private function setConfigFilepath(?string $configFilePath): self
    {
        $this->configFilePath = $configFilePath ?? $this->getConfigFilePath();

        return $this;
    }
}

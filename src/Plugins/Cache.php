<?php

declare(strict_types=1);

namespace Peck\Plugins;

use Composer\Autoload\ClassLoader;
use RuntimeException;

final readonly class Cache
{
    public function __construct(
        private string $cacheDirectory,
    ) {
        if (! is_dir($this->cacheDirectory) && ! mkdir($this->cacheDirectory, 0755, true)) {
            throw new RuntimeException("Could not create cache directory: {$this->cacheDirectory}");
        }
    }

    /**
     * Creates the default instance of Spellchecker.
     */
    public static function default(): self
    {
        $basePath = dirname(array_keys(ClassLoader::getRegisteredLoaders())[0]);

        return new self("{$basePath}/.peck.cache");
    }

    public function get(string $key): mixed
    {
        $cacheFile = $this->getCacheFile($key);

        if (! file_exists($cacheFile)) {
            return null;
        }

        $serializedContents = file_get_contents($cacheFile);

        if ($serializedContents === false) {
            return null;
        }

        return unserialize($serializedContents);
    }

    public function set(string $key, mixed $value): void
    {
        file_put_contents($this->getCacheFile($key), serialize($value));
    }

    public function has(string $key): bool
    {
        return is_readable($this->getCacheFile($key));
    }

    public function getCacheFile(string $key): string
    {
        $separator = str_ends_with($this->cacheDirectory, '/') ? '' : DIRECTORY_SEPARATOR;

        return $this->cacheDirectory.$separator.$key;
    }
}

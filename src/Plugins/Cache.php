<?php

declare(strict_types=1);

namespace Peck\Plugins;

use RuntimeException;

final readonly class Cache
{
    /**
     * The version of the cache.
     */
    private const int VERSION = 1;

    /**
     * Creates a new instance of Cache.
     */
    public function __construct(
        private string $cacheDirectory,
    ) {
        //
    }

    /**
     * Creates the default instance of Spellchecker.
     */
    public static function default(): self
    {
        $basePath = __DIR__.'/../../';

        $cache = new self("{$basePath}/.peck.cache");

        $cache->get('__internal_version') === self::VERSION ?: $cache->flush();

        $cache->set('__internal_version', self::VERSION);

        return $cache;
    }

    /**
     * Gets the value from the cache.
     */
    public function get(string $key): mixed
    {
        $key = $this->getCacheKey($key);

        $cacheFile = $this->getCacheFile($key);

        if (! file_exists($cacheFile)) {
            return null;
        }

        $serializedContents = file_get_contents($cacheFile);

        if ($serializedContents === false || ! $this->isSerialized($serializedContents)) {
            return null;
        }

        return unserialize($serializedContents);
    }

    /**
     * Sets the given value in the cache.
     */
    public function set(string $key, mixed $value): void
    {
        $key = $this->getCacheKey($key);

        file_put_contents($this->getCacheFile($key), serialize($value));
    }

    /**
     * Checks if the cache has the given key.
     */
    public function has(string $key): bool
    {
        $key = $this->getCacheKey($key);

        return is_readable($this->getCacheFile($key));
    }

    /**
     * Gets the cache file for the given key.
     */
    public function getCacheFile(string $key): string
    {
        if (! is_dir($this->cacheDirectory) && ! mkdir($this->cacheDirectory, 0755, true)) {
            throw new RuntimeException("Could not create cache directory: {$this->cacheDirectory}");
        }

        $separator = str_ends_with($this->cacheDirectory, '/') ? '' : DIRECTORY_SEPARATOR;

        return $this->cacheDirectory.$separator.$key;
    }

    /**
     * Gets the cache key for the given key.
     */
    public function getCacheKey(string $key): string
    {
        return md5($key);
    }

    /**
     * Flushes the cache.
     */
    public function flush(): void
    {
        if (is_array($files = glob("{$this->cacheDirectory}/*"))) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }
    }

    /**
     * Checks if the given string is serialized.
     */
    private function isSerialized(string $string): bool
    {
        return $string === serialize(false) || @unserialize($string) !== false;
    }
}

<?php

declare(strict_types=1);

namespace Peck\Plugins;

final readonly class Cache
{
    public function __construct(
        private string $cacheDirectory,
    ) {
        if (! is_dir($this->cacheDirectory)) {
            mkdir($this->cacheDirectory, 0777, true);
        }
    }

    /**
     * Creates the default instance of Spellchecker.
     */
    public static function default(): self
    {
        return new self(__DIR__.'/../../.peck.cache');
    }

    public function get(string $key): mixed
    {
        $cacheFile = $this->getCacheFile($key);

        if (! file_exists($cacheFile)) {
            return null;
        }

        /**
         * @var string
         */
        $serializedContents = file_get_contents($cacheFile);

        return unserialize($serializedContents);
    }

    public function set(string $key, mixed $value): void
    {
        file_put_contents($this->getCacheFile($key), serialize($value));
    }

    public function has(string $key): bool
    {
        return file_exists($this->getCacheFile($key));
    }

    private function getCacheFile(string $key): string
    {
        $separator = str_ends_with($this->cacheDirectory, '/') ? '' : DIRECTORY_SEPARATOR;

        return $this->cacheDirectory.$separator.$key.'';
    }
}

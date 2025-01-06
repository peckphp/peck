<?php

declare(strict_types=1);

namespace Peck\Cache;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @internal
 */
final readonly class CacheManager
{
    /**
     * Creates a new instance of CacheManager.
     */
    public function __construct(private FilesystemAdapter $cache)
    {
        //
    }

    /**
     * Creates the default instance of CacheManager.
     */
    public static function create(string $namespace = 'Peck', int $defaultLifetime = 3600, ?string $cacheDirectory = null): self
    {
        return new self(new FilesystemAdapter($namespace, $defaultLifetime, $cacheDirectory ?? dirname(__DIR__, 5).'/.peck.cache'));
    }

    /**
     * Get the value of the given key from the cache.
     */
    public function get(string $key, ?callable $callback = null): mixed
    {
        return $this->cache->get($key, fn (ItemInterface $item) => $callback ? $callback($item) : null);
    }

    /**
     * Set the value of the given key in the cache.
     */
    public function set(string $key, mixed $value, ?int $lifetime = null): void
    {
        $item = $this->cache->getItem($key);
        $item->set($value);

        if ($lifetime !== null) {
            $item->expiresAfter($lifetime);
        }

        $this->cache->save($item);
    }

    /**
     * Delete the value of the given key from the cache.
     */
    public function delete(string $key): void
    {
        $this->cache->deleteItem($key);
    }

    /**
     * Clear the cache.
     */
    public function clear(string $prefix = ''): void
    {
        $this->cache->clear($prefix);
    }
}

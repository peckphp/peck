<?php

declare(strict_types=1);

namespace Peck\Services\Spellcheckers;

use Peck\Cache;
use Peck\Config;
use Peck\Contracts\Services\Spellchecker;
use Peck\ValueObjects\Misspelling;
use PhpSpellcheck\MisspellingInterface;
use PhpSpellcheck\Spellchecker\Aspell;

final readonly class InMemorySpellchecker implements Spellchecker
{
    /**
     * Creates a new instance of Spellchecker.
     */
    public function __construct(
        private Config $config,
        private Aspell $aspell,
        private Cache $cache,
    ) {
        //
    }

    /**
     * Creates the default instance of Spellchecker.
     */
    public static function default(): self
    {
        return new self(
            Config::instance(),
            Aspell::create(),
            Cache::default(),
        );
    }

    /**
     * Checks of issues in the given text.
     *
     * @return array<int, Misspelling>
     */
    public function check(string $text): array
    {
        $cacheKey = md5($text);
        /**
         * @var array<int, MisspellingInterface>
         */
        $misspellings = $this->cache->has($cacheKey) ? $this->cache->get($cacheKey) : $this->getMisspellings($text);
        $misspellings = $this->filterWhitelistedWords($misspellings);

        return array_map(fn (MisspellingInterface $misspelling): Misspelling => new Misspelling(
            $misspelling->getWord(),
            array_slice($misspelling->getSuggestions(), 0, 4),
        ), $misspellings);
    }

    /**
     * Gets the misspellings from the given text.
     *
     * @return array<int, MisspellingInterface>
     */
    private function getMisspellings(string $text): array
    {
        $misspellings = iterator_to_array($this->aspell->check($text));
        $this->cache->set(md5($text), $misspellings);

        return $misspellings;
    }

    /**
     * Filters the given words against the whitelisted words stored in the configuration.
     *
     * @param  array<int, MisspellingInterface>  $misspellings
     * @return array<int, MisspellingInterface> $misspellings
     */
    private function filterWhitelistedWords(array $misspellings): array
    {
        return array_filter($misspellings, fn (MisspellingInterface $misspelling): bool => ! in_array(
            strtolower($misspelling->getWord()),
            $this->config->whitelistedWords
        ));
    }
}

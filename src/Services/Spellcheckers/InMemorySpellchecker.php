<?php

declare(strict_types=1);

namespace Peck\Services\Spellcheckers;

use Peck\Config;
use Peck\Contracts\Services\Spellchecker;
use Peck\Plugins\Cache;
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
        /**
         * @var array<int, MisspellingInterface>
         */
        $misspellings = $this->cache->has($text) ? $this->cache->get($text) : $this->getMisspellings($text);
        $misspellings = $this->filterWhitelistedWords($misspellings);

        return array_map(fn (MisspellingInterface $misspelling): Misspelling => new Misspelling(
            $misspelling->getWord(),
            $this->takeSuggestions($misspelling),
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
        $this->cache->set($text, $misspellings);

        return $misspellings;
    }

    /**
     * Take the relevant suggestions from the given misspelling.
     *
     * @return array<int, string>
     */
    private function takeSuggestions(MisspellingInterface $misspelling): array
    {
        $suggestions = array_filter($misspelling->getSuggestions(),
            fn (string $suggestion): bool => in_array(preg_match('/[^a-zA-Z]/', $suggestion), [0, false], true)
        );

        return array_slice(array_values(array_unique($suggestions)), 0, 4);
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

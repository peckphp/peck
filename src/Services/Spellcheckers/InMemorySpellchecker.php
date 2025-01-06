<?php

declare(strict_types=1);

namespace Peck\Services\Spellcheckers;

use Peck\Cache\CacheManager;
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
        private CacheManager $cache
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
            CacheManager::create('Peck.InMemorySpellchecker'),
        );
    }

    /**
     * Checks of issues in the given text.
     *
     * @return array<int, Misspelling>
     */
    public function check(string $text): array
    {
        $issues = $this->cache->get(md5('check_'.$text), function () use ($text): array {
            $misspellings = $this->filterWhitelistedWords(iterator_to_array($this->aspell->check($text)));

            return array_map(fn (MisspellingInterface $misspelling): Misspelling => new Misspelling(
                $misspelling->getWord(),
                array_slice($misspelling->getSuggestions(), 0, 4),
            ), $misspellings);
        });

        return is_array($issues) ? $issues : [];
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

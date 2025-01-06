<?php

declare(strict_types=1);

namespace Peck\Services\Spellcheckers;

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
        );
    }

    /**
     * Checks of issues in the given text.
     *
     * @return array<int, Misspelling>
     */
    public function check(string $text): array
    {
        $misspellings = $this->filterWhitelistedWords(iterator_to_array($this->aspell->check($text)));

        return array_map(fn (MisspellingInterface $misspelling): Misspelling => new Misspelling(
            $misspelling->getWord(),
            $this->extractSuggestions($misspelling, count: 4),
        ), $misspellings);
    }

    /**
     * Extracts the suggestions from the given misspelling.
     * Filters words that can't be used in code (e.g. apostrophes).
     *
     * @return array<int, string>
     */
    private function extractSuggestions(MisspellingInterface $misspelling, int $count): array
    {
        $filteredSuggestions = array_filter($misspelling->getSuggestions(),
            fn (string $suggestion): bool => in_array(preg_match('/[^a-zA-Z]/', $suggestion), [0, false], true)
        );

        return array_slice(array_values($filteredSuggestions), 0, $count);
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

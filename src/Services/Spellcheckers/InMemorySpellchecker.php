<?php

declare(strict_types=1);

namespace Peck\Services\Spellcheckers;

use Peck\Contracts\Services\Spellchecker;
use Peck\Services\WhitelistManager;
use Peck\ValueObjects\Misspelling;
use PhpSpellcheck\MisspellingInterface;
use PhpSpellcheck\Spellchecker\Aspell;

final readonly class InMemorySpellchecker implements Spellchecker
{
     /**
     * Creates a new instance of Spellchecker.
     */
    public function __construct(
        private Aspell $aspell,
        private WhitelistManager $whitelistManager,
    ) {
        //
    }
    
    /**
     * Creates the default instance of Spellchecker.
     */
    public static function default(): self
    {
        return new self(
            Aspell::create(),
            new WhitelistManager(getcwd())
        );
    }
    
    /**
     * Checks of issues in the given text.
     *
     * @return array<int, Misspelling>
     */
    public function check(string $text): array
    {
        $misspellings = $this->aspell->check($text);

        return array_values(array_filter(
            array_map(
                fn (MisspellingInterface $misspelling): Misspelling => new Misspelling(
            $misspelling->getWord(),
            array_slice($misspelling->getSuggestions(), 0, 4),
                ),
                iterator_to_array($misspellings)
            ),
            fn (Misspelling $misspelling): bool => !$this->whitelistManager->isWhitelisted($misspelling->word)
        ));
    }
}

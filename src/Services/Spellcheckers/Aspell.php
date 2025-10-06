<?php

declare(strict_types=1);

namespace Peck\Services\Spellcheckers;

use Peck\Config;
use Peck\Contracts\Services\Spellchecker;
use Peck\Plugins\Cache;
use Peck\ValueObjects\Misspelling;
use Symfony\Component\Process\Process;

final class Aspell implements Spellchecker
{
    /**
     * The process instance, if any.
     */
    private static ?Process $process = null;

    /**
     * Creates a new instance of Spellchecker.
     */
    public function __construct(
        private readonly Config $config,
        private readonly Cache $cache,
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
        /** @var array<int, Misspelling>|null $misspellings */
        $misspellings = $this->cache->has($text) ? $this->cache->get($text) : $this->getMisspellings($text);

        if (! is_array($misspellings)) {
            $misspellings = $this->getMisspellings($text);
        }

        return array_filter(
            $misspellings,
            fn (Misspelling $misspelling): bool => ! $this->config->isWordIgnored($misspelling->word),
        );
    }

    /**
     * Gets the misspellings from the given text.
     *
     * @return array<int, Misspelling>
     */
    private function getMisspellings(string $text): array
    {
        $misspellings = $this->run($text);

        $this->cache->set($text, $misspellings);

        return $misspellings;
    }

    /**
     * Take the relevant suggestions from the given misspelling.
     *
     * @param  array<int, string>  $suggestions
     * @return array<int, string>
     */
    private function takeSuggestions(array $suggestions): array
    {
        $suggestions = array_filter(
            $suggestions,
            fn (string $suggestion): bool => in_array(preg_match('/[^a-zA-Z]/', $suggestion), [0, false], true)
        );

        return array_slice(array_values(array_unique($suggestions)), 0, 4);
    }

    /**
     * Runs the Aspell command with the given text and returns the suggestions, if any.
     *
     * @return array<int, Misspelling>
     */
    private function run(string $text): array
    {
        $process = self::$process ??= $this->createProcess();

        $process->setInput($text);

        $process->mustRun();

        $output = $process->getOutput();

        return array_values(array_map(fn (string $line): Misspelling => $this->createMisspellingFromLine($line), $this->getLinesFromOutput($output)));
    }

    /**
     * Returns the lines from the output of the Aspell command.
     * Lines with suggestions start with '&' and lines without suggestions start with '#'.
     *
     * @return array<int,string>
     */
    private function getLinesFromOutput(string $output): array
    {
        return array_filter(
            explode(PHP_EOL, $output),
            fn (string $line): bool => str_starts_with($line, '&') || str_starts_with($line, '#')
        );
    }

    /**
     * Creates a new instance of Misspelling from the given line.
     * The suggestions are empty if there are no suggestions.
     */
    private function createMisspellingFromLine(string $line): Misspelling
    {
        [$wordMetadataAsString, $suggestionsAsString] = explode(':', trim($line)) + [1 => null];

        $word = explode(' ', (string) $wordMetadataAsString)[1];

        // If there are no suggestions, return an empty array
        if ($suggestionsAsString === null || ($suggestionsAsString === '' || $suggestionsAsString === '0')) {
            return new Misspelling($word, []);
        }

        $suggestions = explode(', ', trim($suggestionsAsString));

        return new Misspelling($word, $this->takeSuggestions($suggestions));
    }

    /**
     * Creates a new instance of Process.
     */
    private function createProcess(): Process
    {
        $process = new Process([
            'aspell',
            '--encoding',
            'utf-8',
            '-a',
            '--ignore-case',
            '--lang='.$this->config->getLanguage(),
        ]);

        $process->setTimeout(0);

        return $process;
    }
}

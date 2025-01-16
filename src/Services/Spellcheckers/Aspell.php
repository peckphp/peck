<?php

declare(strict_types=1);

namespace Peck\Services\Spellcheckers;

use Peck\Config;
use Peck\Contracts\Services\Spellchecker;
use Peck\Plugins\Cache;
use Peck\ValueObjects\Misspelling;
use Symfony\Component\Process\Process;

final readonly class Aspell implements Spellchecker
{
    /**
     * Creates a new instance of Spellchecker.
     */
    public function __construct(
        private Config $config,
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

        return array_filter($misspellings,
            fn (Misspelling $misspelling): bool => ! $this->config->isWordIgnored($misspelling->word),
        );
    }

    /**
     * Parses the output from the Aspell command.
     *
     * @return array<int, Misspelling>
     */
    private function parseOutput(string $output): array
    {
        return array_values(array_map(
            function (string $line): Misspelling {
                [$wordMetadataAsString, $suggestionsAsString] = explode(':', trim($line));
                $word = explode(' ', $wordMetadataAsString)[1];
                $suggestions = explode(', ', trim($suggestionsAsString));

                return new Misspelling($word, $this->takeSuggestions($suggestions));
            },
            array_filter(
                explode(PHP_EOL, $output),
                fn (string $line): bool => str_starts_with($line, '&')
            )
        ));
    }

    /**
     * Gets the misspellings from the given text.
     *
     * @return array<int, Misspelling>
     */
    private function getMisspellings(string $text): array
    {
        $chunks = array_filter(explode("\n", $text));

        $processes = array_map(
            fn (string $chunk): Process => $this->createProcessForChunk($chunk), $chunks
        );

        $misspellings = $this->runProcessesInParallel($processes);

        $this->cache->set($text, $misspellings);

        return $misspellings;
    }

    private function createProcessForChunk(string $chunk): Process
    {
        $process = new Process([
            'aspell',
            '--encoding',
            'utf-8',
            '-a',
            '--ignore-case',
            '--lang=en_US',
        ]);

        $process->setInput($chunk);

        return $process;
    }

    /**
     * Runs the given processes in parallel.
     *
     * @param  array<int, Process>  $processes
     * @return array<int, Misspelling>
     */
    private function runProcessesInParallel(array $processes): array
    {
        array_walk($processes, fn (Process $process) => $process->start());

        return array_reduce($processes, function (array $misspellings, Process $process): array {
            $process->wait();

            return array_merge($misspellings, $this->parseOutput($process->getOutput()));
        }, []);
    }

    /**
     * Take the relevant suggestions from the given misspelling.
     *
     * @param  array<int, string>  $suggestions
     * @return array<int, string>
     */
    private function takeSuggestions(array $suggestions): array
    {
        $suggestions = array_filter($suggestions,
            fn (string $suggestion): bool => in_array(preg_match('/[^a-zA-Z]/', $suggestion), [0, false], true)
        );

        return array_slice(array_values(array_unique($suggestions)), 0, 4);
    }
}

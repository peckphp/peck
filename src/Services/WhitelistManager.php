<?php

declare(strict_types=1);

namespace Peck\Services;

/**
 * @internal
 */
final readonly class WhitelistManager
{
    private const WHITELIST_FILE = '.peck-whitelist.json';

    public function __construct(
        private string $baseDirectory
    ) {
    }

    /**
     * Add a word to the whitelist
     */
    public function add(string $word): void
    {
        $whitelist = $this->load();

        if (!in_array($word, $whitelist, true)) {
            $whitelist[] = $word;
            $this->save($whitelist);
        }
    }

    /**
     * Check if a word is whitelisted
     */
    public function isWhitelisted(string $word): bool
    {
        return in_array(strtolower($word), $this->load(), true);
    }

    /**
     * Load the whitelist from storage
     *
     * @return array<int, string>
     */
    private function load(): array
    {
        $path = $this->getWhitelistPath();

        if (!file_exists($path)) {
            return [];
        }

        $content = file_get_contents($path);

        return json_decode($content, true) ?? [];
    }

    /**
     * Save the whitelist to storage
     *
     * @param array<int, string> $whitelist
     */
    private function save(array $whitelist): void
    {
        file_put_contents(
            $this->getWhitelistPath(),
            json_encode($whitelist, JSON_PRETTY_PRINT)
        );
    }

    private function getWhitelistPath(): string
    {
        return $this->baseDirectory . DIRECTORY_SEPARATOR . self::WHITELIST_FILE;
    }
}

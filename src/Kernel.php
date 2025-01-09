<?php

declare(strict_types=1);

namespace Peck;

final readonly class Kernel
{
    /**
     * Creates a new instance of Kernel.
     */
    public function __construct(
        private Scanner $scanner
    ) {
        //
    }

    /**
     * Creates the default instance of Kernel.
     */
    public static function default(): self
    {
        return new self(
            Scanner::default()
        );
    }

    /**
     * Handles the given parameters.
     *
     * @param  array{directory: string}  $parameters
     * @return array<int, ValueObjects\Issue>
     */
    public function handle(array $parameters): array
    {
        return $this->scanner->scan($parameters);
    }
}

<?php

declare(strict_types=1);

namespace Peck\Support;

final readonly class NotPaths
{
    /**
     * Gets the not paths for the given in / paths.
     *
     * @param  array<int, string>  $paths
     * @return array<int, string>
     */
    public static function get(string $in, array $paths): array
    {
        $paths = array_map(
            fn (string $path): string => (realpath(ProjectPath::get())).'/'.ltrim(ltrim($path, '/'), './'),
            $paths,
        );

        return array_map(
            fn (string $path): string => ltrim(str_replace((string) realpath($in), '', $path), '/'),
            $paths,
        );
    }
}

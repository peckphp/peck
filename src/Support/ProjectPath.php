<?php

declare(strict_types=1);

namespace Peck\Support;

use Composer\Autoload\ClassLoader;

final readonly class ProjectPath
{
    /**
     * Gets the path of the project.
     */
    public static function get(): string
    {
        return dirname(array_keys(ClassLoader::getRegisteredLoaders())[0]);
    }
}

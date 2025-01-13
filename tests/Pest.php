<?php

declare(strict_types=1);

use Peck\Config;

uses()->beforeEach(function (): void {
    // REMOVE EVEN IF IT IS NOT EMPTY
    $dir = __DIR__.'/../.peck.cache';

    if (is_dir($dir)) {
        $files = scandir($dir);

        foreach ($files as $file) {
            if ($file === '.') {
                continue;
            }
            if ($file === '..') {
                continue;
            }
            unlink("$dir/$file");
        }
    }

    Config::flush();
})->in(__DIR__);

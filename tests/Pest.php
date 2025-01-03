<?php

declare(strict_types=1);

use Peck\Config;

pest()->beforeEach(function (): void {
    Config::flush();
});

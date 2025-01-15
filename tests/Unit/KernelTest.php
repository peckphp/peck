<?php

declare(strict_types=1);

use Peck\Kernel;

it('handles multiple checkers', function (): void {
    $kernel = Kernel::default();

    $issues = $kernel->handle([
        'directory' => __DIR__.'/../Fixtures',
        'onSuccess' => fn (): null => null,
        'onFailure' => fn (): null => null,
    ]);

    expect($issues)->toHaveCount(37);
});

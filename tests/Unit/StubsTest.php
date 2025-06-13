<?php

declare(strict_types=1);

test('stub files', function () {
    expect('stubs/presets')
        ->toReturnUnique()
        ->toBeOrdered();
});

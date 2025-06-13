<?php

declare(strict_types=1);

test('stub files', function (): void {
    expect('stubs/presets')
        ->toReturnUnique();

    expect('stubs/presets/base.stub')
        ->toReturnLowercase()
        ->toBeOrdered();

    expect('stubs/presets/iso4217.stub')
        ->toBeOrdered();
});

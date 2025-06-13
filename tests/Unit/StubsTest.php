<?php

declare(strict_types=1);

test('stub files', function (): void {
    expect('stubs/presets/base.stub')
        ->toReturnUnique()
        ->toReturnLowercase()
        ->toBeOrdered();

    expect('stubs/presets/iso3166.stub')
        ->toReturnUnique();

    expect('stubs/presets/iso4217.stub')
        ->toReturnUnique()
        ->toBeOrdered();
});

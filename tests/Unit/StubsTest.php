<?php

declare(strict_types=1);

test('stub files', function () {
    expect('stubs/presets/base.stub')
        ->toReturnUnique()
        ->toReturnLowercase()
        ->toBeOrdered();

    expect('stubs/presets/iso3166.stub')
        ->toReturnUnique()
        ->not->toReturnLowercase();

    expect('stubs/presets/iso4217.stub')
        ->toReturnUnique()
        ->not->toReturnLowercase()
        ->toBeOrdered();
});

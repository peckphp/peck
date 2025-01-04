<?php

declare(strict_types=1);

use Peck\Services\NameParser;

it('can handle pascal case', function (): void {
    $result = (new NameParser)->parse('MyClassName');

    expect($result)->toBeString()->toBe('my class name');
});

it('can handle camel case', function (): void {
    $result = (new NameParser)->parse('myMethodOrVariableName');

    expect($result)->toBeString()->toBe('my method or variable name');
});

it('can handle snake case', function (): void {
    $result = (new NameParser)->parse('snake_case');

    expect($result)->toBeString()->toBe('snake case');
});

it('can handle screaming snake case', function (): void {
    $result = (new NameParser)->parse('MY_CLASS_CONSTANT');

    expect($result)->toBeString()->toBe('my class constant');
});

it('can handle kebab case', function (): void {
    $result = (new NameParser)->parse('some-endpoint-name');

    expect($result)->toBeString()->toBe('some endpoint name');
});

it('can handle magic functions', function (): void {
    $result = (new NameParser)->parse('__construct');

    expect($result)->toBeString()->toBe('construct');
});

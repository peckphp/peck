<?php

declare(strict_types=1);

use Peck\Support\SpellcheckFormatter;

it('can handle pascal case', function (): void {
    $result = SpellcheckFormatter::format('MyClassName');

    expect($result)->toBeString()->toBe('my class name');
});

it('can handle camel case', function (): void {
    $result = SpellcheckFormatter::format('myMethodOrVariableName');

    expect($result)->toBeString()->toBe('my method or variable name');
});

it('can handle snake case', function (): void {
    $result = SpellcheckFormatter::format('snake_case');

    expect($result)->toBeString()->toBe('snake case');
});

it('can handle screaming snake case', function (): void {
    $result = SpellcheckFormatter::format('MY_CLASS_CONSTANT');

    expect($result)->toBeString()->toBe('my class constant');
});

it('can handle kebab case', function (): void {
    $result = SpellcheckFormatter::format('some-endpoint-name');

    expect($result)->toBeString()->toBe('some endpoint name');
});

it('can handle magic functions', function (): void {
    $result = SpellcheckFormatter::format('__construct');

    expect($result)->toBeString()->toBe('construct');
});

it('can handle abbreviations', function (): void {
    $result = SpellcheckFormatter::format('HTTPController');

    expect($result)->toBeString()->toBe('http controller');
});

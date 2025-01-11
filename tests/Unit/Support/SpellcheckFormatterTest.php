<?php

declare(strict_types=1);

use Peck\Support\SpellcheckFormatter;

it('can handle pascal case', function (): void {
    $result = SpellcheckFormatter::format('MyClassName');

    expect($result)->toBeString()->toBe('My Class Name');
});

it('can handle camel case', function (): void {
    $result = SpellcheckFormatter::format('myMethodOrVariableName');

    expect($result)->toBeString()->toBe('my Method Or Variable Name');
});

it('can handle snake case', function (): void {
    $result = SpellcheckFormatter::format('snake_case');

    expect($result)->toBeString()->toBe('snake case');
});

it('can handle screaming snake case', function (): void {
    $result = SpellcheckFormatter::format('MY_CLASS_CONSTANT');

    expect($result)->toBeString()->toBe('MY CLASS CONSTANT');
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
    $result = SpellcheckFormatter::format('exportSAPFiles');

    expect($result)->toBeString()->toBe('export SAP Files');

    $result = SpellcheckFormatter::format('getPersonsFromUSAOrSAP');

    expect($result)->toBeString()->toBe('get Persons From USA Or SAP');
});

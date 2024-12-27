<?php

use Peck\Checkers\ClassMethodNameChecker;
use Peck\Services\Spellcheckers\InMemorySpellchecker;

it('does not detect issues in the given directory', function (): void {
    $checker = new ClassMethodNameChecker(
        InMemorySpellchecker::default(),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../../src',
    ]);

    expect($issues)->toBeEmpty();
});

it('detects issues in the given directory', function (): void {
    $checker = new ClassMethodNameChecker(
        InMemorySpellchecker::default(),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../Fixtures',
    ]);

    expect($issues)->toHaveCount(1)
        ->and($issues[0]->file)->toEndWith('tests/Fixtures/FolderWithTypoos/ClassMethodNameWithTypo.php')
        ->and($issues[0]->line)->toBe(0)
        ->and($issues[0]->misspelling->word)->toBe('constructt')
        ->and($issues[0]->misspelling->suggestions)->toBe([
            'construct',
            'constructor',
            'constrict',
            'constructs',
        ]);
});

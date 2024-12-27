<?php

use Peck\Checkers\ClassFunctionNameChecker;
use Peck\Config;
use Peck\Services\Spellcheckers\InMemorySpellchecker;
use PhpSpellcheck\Spellchecker\Aspell;

it('does not detect issues in the given directory', function (): void {
    $checker = new ClassFunctionNameChecker(
        Config::instance(),
        InMemorySpellchecker::default(),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../../src',
    ]);

    expect($issues)->toBeEmpty();
});

it('detects issues in the given directory', function (): void {
    $checker = new ClassFunctionNameChecker(
        Config::instance(),
        InMemorySpellchecker::default(),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../Fixtures',
    ]);

    expect($issues)->toHaveCount(4)
        ->and($issues[0]->file)->toEndWith('tests/Fixtures/ClassWithTypos.php')
        ->and($issues[0]->line)->toBe(3)
        ->and($issues[0]->misspelling->word)->toBe('typoss')
        ->and($issues[0]->misspelling->suggestions)->toBe([
            'typos',
            'typo\'s',
            'types',
            'type\'s',
        ])->and($issues[1]->file)->toEndWith('tests/Fixtures/ClassWithTypos.php')
        ->and($issues[1]->line)->toBe(5)
        ->and($issues[1]->misspelling->word)->toBe('withh')
        ->and($issues[1]->misspelling->suggestions)->toBe([
            'with',
            'withe',
            'witch',
            'wither',
        ])->and($issues[2]->file)->toEndWith('tests/Fixtures/ClassWithTypos.php')
        ->and($issues[2]->line)->toBe(7)
        ->and($issues[2]->misspelling->word)->toBe('func')
        ->and($issues[2]->misspelling->suggestions)->toBe([
            'fun',
            'funk',
            'fund',
            'fink',
        ])->and($issues[3]->file)->toEndWith('tests/Fixtures/FolderWithTypoos/FolderThatShouldBeIgnored/FileThatShoudBeIgnoredBecauseItsInsideWhitelistedFolder.php')
        ->and($issues[3]->line)->toBe(3)
        ->and($issues[3]->misspelling->word)->toBe('typoss')
        ->and($issues[3]->misspelling->suggestions)->toBe([
            'typos',
            'typo\'s',
            'types',
            'type\'s',
        ]);
});

it('detects issues in the given directory, but ignores the whitelisted words', function (): void {
    $config = new Config(
        whitelistedWords: ['Func'],
    );

    $checker = new ClassFunctionNameChecker(
        $config,
        new InMemorySpellchecker(
            $config,
            Aspell::create(),
        ),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../Fixtures',
    ]);

    expect($issues)->toHaveCount(3)
        ->and($issues[0]->file)->toEndWith('tests/Fixtures/ClassWithTypos.php')
        ->and($issues[0]->line)->toBe(3)
        ->and($issues[0]->misspelling->word)->toBe('typoss')
        ->and($issues[0]->misspelling->suggestions)->toBe([
            'typos',
            'typo\'s',
            'types',
            'type\'s',
        ])->and($issues[1]->file)->toEndWith('tests/Fixtures/ClassWithTypos.php')
        ->and($issues[1]->line)->toBe(5)
        ->and($issues[1]->misspelling->word)->toBe('withh')
        ->and($issues[1]->misspelling->suggestions)->toBe([
            'with',
            'withe',
            'witch',
            'wither',
        ]);
});

it('detects issues in the given directory, but ignores the whitelisted directories', function (): void {
    $checker = new ClassFunctionNameChecker(
        new Config(
            whitelistedDirectories: ['FolderThatShouldBeIgnored'],
        ),
        InMemorySpellchecker::default(),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../Fixtures',
    ]);

    expect($issues)->toHaveCount(3)
        ->and($issues[0]->file)->toEndWith('tests/Fixtures/ClassWithTypos.php')
        ->and($issues[0]->line)->toBe(3)
        ->and($issues[0]->misspelling->word)->toBe('typoss')
        ->and($issues[0]->misspelling->suggestions)->toBe([
            'typos',
            'typo\'s',
            'types',
            'type\'s',
        ])->and($issues[1]->file)->toEndWith('tests/Fixtures/ClassWithTypos.php')
        ->and($issues[1]->line)->toBe(5)
        ->and($issues[1]->misspelling->word)->toBe('withh')
        ->and($issues[1]->misspelling->suggestions)->toBe([
            'with',
            'withe',
            'witch',
            'wither',
        ]);
});

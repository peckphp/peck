<?php

use Peck\Checkers\MethodNameChecker;
use Peck\Config;
use Peck\Services\Spellcheckers\InMemorySpellchecker;
use PhpSpellcheck\Spellchecker\Aspell;

it('does not detect issues in the given directory', function (): void {
    $checker = new MethodNameChecker(
        Config::instance(),
        InMemorySpellchecker::default(),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../../src',
    ]);

    expect($issues)->toBeEmpty();
});

it('detects issues in the given directory', function (): void {
    $checker = new MethodNameChecker(
        Config::instance(),
        InMemorySpellchecker::default(),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../Fixtures',
    ]);

    expect($issues)->toHaveCount(3)
        ->and($issues[0]->file)->toEndWith('tests/Fixtures/FolderWithTypoos/FileWithMethodTypos.php')
        ->and($issues[0]->line)->toBe(5)
        ->and($issues[0]->misspelling->word)->toBe('spellled')
        ->and($issues[0]->misspelling->suggestions)->toBe([
            'spelled',
            'spilled',
            'spell led',
            'spell-led',
        ])->and($issues[1]->file)->toEndWith('tests/Fixtures/FolderWithTypoos/FileWithMethodTypos.php')
        ->and($issues[1]->line)->toBe(11)
        ->and($issues[1]->misspelling->word)->toBe('spellled')
        ->and($issues[1]->misspelling->suggestions)->toBe([
            'spelled',
            'spilled',
            'spell led',
            'spell-led',
        ])->and($issues[2]->file)->toEndWith('tests/Fixtures/FolderWithTypoos/FolderThatShouldBeIgnored/FileThatShoudBeIgnoredBecauseItsInsideWhitelistedFolder.php')
        ->and($issues[2]->line)->toBe(4)
        ->and($issues[2]->misspelling->word)->toBe('spellling')
        ->and($issues[2]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spell ling',
            'spell-ling',
        ]);
});

it('detects issues in the given directory, but ignores the whitelisted words', function (): void {
    $config = new Config(
        whitelistedWords: ['spellling'],
    );

    $checker = new MethodNameChecker(
        $config,
        new InMemorySpellchecker(
            $config,
            Aspell::create(),
        ),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../Fixtures',
    ]);

    expect($issues)->toHaveCount(2)
        ->and($issues[0]->file)->toEndWith('tests/Fixtures/FolderWithTypoos/FileWithMethodTypos.php')
        ->and($issues[0]->line)->toBe(5)
        ->and($issues[0]->misspelling->word)->toBe('spellled')
        ->and($issues[0]->misspelling->suggestions)->toBe([
            'spelled',
            'spilled',
            'spell led',
            'spell-led',
        ])->and($issues[1]->file)->toEndWith('tests/Fixtures/FolderWithTypoos/FileWithMethodTypos.php')
        ->and($issues[1]->line)->toBe(11)
        ->and($issues[1]->misspelling->word)->toBe('spellled')
        ->and($issues[1]->misspelling->suggestions)->toBe([
            'spelled',
            'spilled',
            'spell led',
            'spell-led',
        ]);
});

it('detects issues in the given directory, but ignores the whitelisted directories', function (): void {
    $checker = new MethodNameChecker(
        new Config(
            whitelistedDirectories: ['FolderThatShouldBeIgnored'],
        ),
        InMemorySpellchecker::default(),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../Fixtures',
    ]);

    expect($issues)->toHaveCount(2)
        ->and($issues[0]->file)->toEndWith('tests/Fixtures/FolderWithTypoos/FileWithMethodTypos.php')
        ->and($issues[0]->line)->toBe(5)
        ->and($issues[0]->misspelling->word)->toBe('spellled')
        ->and($issues[0]->misspelling->suggestions)->toBe([
            'spelled',
            'spilled',
            'spell led',
            'spell-led',
        ])->and($issues[1]->file)->toEndWith('tests/Fixtures/FolderWithTypoos/FileWithMethodTypos.php')
        ->and($issues[1]->line)->toBe(11)
        ->and($issues[1]->misspelling->word)->toBe('spellled')
        ->and($issues[1]->misspelling->suggestions)->toBe([
            'spelled',
            'spilled',
            'spell led',
            'spell-led',
        ]);
});

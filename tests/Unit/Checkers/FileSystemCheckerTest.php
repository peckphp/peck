<?php

declare(strict_types=1);

use Peck\Checkers\FileSystemChecker;
use Peck\Config;
use Peck\Plugins\Cache;
use Peck\Services\Spellcheckers\InMemorySpellchecker;
use PhpSpellcheck\Spellchecker\Aspell;

it('does not detect issues in the given directory', function (): void {
    $checker = new FileSystemChecker(
        Config::instance(),
        InMemorySpellchecker::default(),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../../src',
    ]);

    expect($issues)->toBeEmpty();
});

it('detects issues in the given directory', function (): void {
    $checker = new FileSystemChecker(
        Config::instance(),
        InMemorySpellchecker::default(),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../Fixtures',
    ]);

    expect($issues)->toHaveCount(4)
        ->and($issues[0]->file)->toEndWith('tests/Fixtures/FolderWithTypoos')
        ->and($issues[0]->line)->toBe(0)
        ->and($issues[0]->misspelling->word)->toBe('Typoos')
        ->and($issues[0]->misspelling->suggestions)->toBe([
            'Typos',
            'Types',
            'Tops',
            'Poos',
        ])->and($issues[1]->file)->toEndWith('tests/Fixtures/FolderWithTypoos/FileThatShouldBeIgnroed.php')
        ->and($issues[1]->line)->toBe(0)
        ->and($issues[1]->misspelling->word)->toBe('Ignroed')
        ->and($issues[1]->misspelling->suggestions)->toBe([
            'Ignored',
            'Ignores',
            'Ignore',
            'Inroad',
        ])->and($issues[2]->file)->toEndWith('tests/Fixtures/FolderWithTypoos/FileWithTppyo.php')
        ->and($issues[2]->line)->toBe(0)
        ->and($issues[2]->misspelling->word)->toBe('Tppyo')
        ->and($issues[2]->misspelling->suggestions)->toBe([
            'Typo',
            'Tokyo',
            'Typos',
            'Topi',
        ])->and($issues[3]->file)->toEndWith('tests/Fixtures/FolderWithTypoos/FolderThatShouldBeIgnored/FileThatShoudBeIgnoredBecauseItsInsideWhitelistedFolder.php')
        ->and($issues[3]->line)->toBe(0)
        ->and($issues[3]->misspelling->word)->toBe('Shoud')
        ->and($issues[3]->misspelling->suggestions)->toBe([
            'Should',
            'Shroud',
            'Shod',
            'Shout',
        ]);
});

it('detects issues in the given directory, but Ignores the whitelisted words', function (): void {
    $config = new Config(
        whitelistedWords: ['Ignroed'],
    );

    $checker = new FileSystemChecker(
        $config,
        new InMemorySpellchecker(
            $config,
            Aspell::create(),
            Cache::default(),
        ),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../Fixtures',
    ]);

    expect($issues)->toHaveCount(8)
        ->and($issues[0]->file)->toEndWith('tests/Fixtures/EnumsToTest')
        ->and($issues[0]->line)->toBe(0)
        ->and($issues[0]->misspelling->word)->toBe('Enums')
        ->and($issues[0]->misspelling->suggestions)->toBe([
            'Enemas',
            'Animus',
            'Emus',
            'Ems',
        ])->and($issues[1]->file)->toEndWith('tests/Fixtures/EnumsToTest/BackendEnumWithTypoErrors.php')
        ->and($issues[1]->line)->toBe(0)
        ->and($issues[1]->misspelling->word)->toBe('Backend')
        ->and($issues[1]->misspelling->suggestions)->toBe([
            'Backed',
            'Bookend',
            'Blackened',
            'Beckoned',
        ])->and($issues[2]->file)->toEndWith('tests/Fixtures/EnumsToTest/BackendEnumWithTypoErrors.php')
        ->and($issues[2]->line)->toBe(0)
        ->and($issues[2]->misspelling->word)->toBe('Enum')
        ->and($issues[2]->misspelling->suggestions)->toBe([
            'Enema',
            'Enemy',
            'Emu',
            'Anime',
        ])->and($issues[3]->file)->toEndWith('tests/Fixtures/EnumsToTest/FolderThatShouldBeIgnored/EnumWithTypoErrors.php')
        ->and($issues[3]->line)->toBe(0)
        ->and($issues[3]->misspelling->word)->toBe('Enum')
        ->and($issues[3]->misspelling->suggestions)->toBe([
            'Enema',
            'Enemy',
            'Emu',
            'Anime',
        ])->and($issues[4]->file)->toEndWith('tests/Fixtures/EnumsToTest/UnitEnumWithTypoErrors.php')
        ->and($issues[4]->line)->toBe(0)
        ->and($issues[4]->misspelling->word)->toBe('Enum')
        ->and($issues[4]->misspelling->suggestions)->toBe([
            'Enema',
            'Enemy',
            'Emu',
            'Anime',
        ])->and($issues[5]->file)->toEndWith('tests/Fixtures/FolderWithTypoos')
        ->and($issues[5]->line)->toBe(0)
        ->and($issues[5]->misspelling->word)->toBe('Typoos')
        ->and($issues[5]->misspelling->suggestions)->toBe([
            'Typos',
            'Types',
            'Tops',
            'Poos',
        ])->and($issues[6]->file)->toEndWith('tests/Fixtures/FolderWithTypoos/FileWithTppyo.php')
        ->and($issues[6]->line)->toBe(0)
        ->and($issues[6]->misspelling->word)->toBe('Tppyo')
        ->and($issues[6]->misspelling->suggestions)->toBe([
            'Typo',
            'Tokyo',
            'Typos',
            'Topi',
        ])->and($issues[7]->file)->toEndWith('tests/Fixtures/FolderWithTypoos/FolderThatShouldBeIgnored/FileThatShoudBeIgnoredBecauseItsInsideWhitelistedFolder.php')
        ->and($issues[7]->line)->toBe(0)
        ->and($issues[7]->misspelling->word)->toBe('Shoud')
        ->and($issues[7]->misspelling->suggestions)->toBe([
            'Should',
            'Shroud',
            'Shod',
            'Shout',
        ]);
});

it('detects issues in the given directory, but Ignores the whitelisted directories', function (): void {
    $checker = new FileSystemChecker(
        new Config(
            whitelistedDirectories: ['FolderThatShouldBeIgnored'],
        ),
        InMemorySpellchecker::default(),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../Fixtures',
    ]);

    expect($issues)->toHaveCount(3)
        ->and($issues[0]->file)->toEndWith('tests/Fixtures/FolderWithTypoos')
        ->and($issues[0]->line)->toBe(0)
        ->and($issues[0]->misspelling->word)->toBe('Typoos')
        ->and($issues[0]->misspelling->suggestions)->toBe([
            'Typos',
            'Types',
            'Tops',
            'Poos',
        ])->and($issues[1]->file)->toEndWith('tests/Fixtures/FolderWithTypoos/FileThatShouldBeIgnroed.php')
        ->and($issues[1]->line)->toBe(0)
        ->and($issues[1]->misspelling->word)->toBe('Ignroed')
        ->and($issues[1]->misspelling->suggestions)->toBe([
            'Ignored',
            'Ignores',
            'Ignore',
            'Inroad',
        ])->and($issues[2]->file)->toEndWith('tests/Fixtures/FolderWithTypoos/FileWithTppyo.php')
        ->and($issues[2]->line)->toBe(0)
        ->and($issues[2]->misspelling->word)->toBe('Tppyo')
        ->and($issues[2]->misspelling->suggestions)->toBe([
            'Typo',
            'Tokyo',
            'Typos',
            'Topi',
        ]);
});

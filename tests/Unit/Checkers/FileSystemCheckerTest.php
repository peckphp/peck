<?php

declare(strict_types=1);

use Peck\Checkers\FileSystemChecker;
use Peck\Config;
use Peck\Plugins\Cache;
use Peck\Services\Spellcheckers\Aspell;

it('does not detect issues in the given directory', function (): void {
    $checker = new FileSystemChecker(
        Config::instance(),
        Aspell::default(),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../../src',
        'onProgress' => fn (): null => null,
    ]);

    expect($issues)->toBeEmpty();
});

it('detects issues in the given directory', function (): void {
    $checker = new FileSystemChecker(
        Config::instance(),
        Aspell::default(),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../Fixtures',
        'onProgress' => fn (): null => null,
    ]);

    expect($issues)->toHaveCount(4)
        ->and($issues[0]->file)->toEndWith('tests/Fixtures/FolderWithTypoos')
        ->and($issues[0]->line)->toBe(0)
        ->and($issues[0]->misspelling->word)->toBe('typoos')
        ->and($issues[0]->misspelling->suggestions)->toBe([
            'typos',
            'types',
            'tops',
            'poos',
        ])->and($issues[1]->file)->toEndWith('tests/Fixtures/FolderWithTypoos/FileThatShouldBeIgnroed.php')
        ->and($issues[1]->line)->toBe(0)
        ->and($issues[1]->misspelling->word)->toBe('ignroed')
        ->and($issues[1]->misspelling->suggestions)->toBe([
            'ignored',
            'ignores',
            'ignore',
            'inroad',
        ])->and($issues[2]->file)->toEndWith('tests/Fixtures/FolderWithTypoos/FileWithTppyo.php')
        ->and($issues[2]->line)->toBe(0)
        ->and($issues[2]->misspelling->word)->toBe('tppyo')
        ->and($issues[2]->misspelling->suggestions)->toBe([
            'typo',
            'Tokyo',
            'typos',
            'topi',
        ])->and($issues[3]->file)->toEndWith('tests/Fixtures/FolderWithTypoos/FolderThatShouldBeIgnored/FileThatShoudBeIgnoredBecauseItsInsideWhitelistedFolder.php')
        ->and($issues[3]->line)->toBe(0)
        ->and($issues[3]->misspelling->word)->toBe('shoud')
        ->and($issues[3]->misspelling->suggestions)->toBe([
            'should',
            'shroud',
            'shod',
            'shout',
        ]);
});

it('detects issues in the given directory, but ignores the whitelisted words', function (): void {
    $config = new Config(
        whitelistedWords: ['Ignroed'],
    );

    $checker = new FileSystemChecker(
        $config,
        new Aspell(
            $config,
            Cache::default(),
        ),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../Fixtures',
        'onProgress' => fn (): null => null,
    ]);

    expect($issues)->toHaveCount(8)
        ->and($issues[0]->file)->toEndWith('tests/Fixtures/EnumsToTest')
        ->and($issues[0]->line)->toBe(0)
        ->and($issues[0]->misspelling->word)->toBe('enums')
        ->and($issues[0]->misspelling->suggestions)->toBe([
            'enemas',
            'animus',
            'emus',
            'ems',
        ])->and($issues[1]->file)->toEndWith('tests/Fixtures/EnumsToTest/BackendEnumWithTypoErrors.php')
        ->and($issues[1]->line)->toBe(0)
        ->and($issues[1]->misspelling->word)->toBe('backend')
        ->and($issues[1]->misspelling->suggestions)->toBe([
            'backed',
            'bookend',
            'blackened',
            'beckoned',
        ])->and($issues[2]->file)->toEndWith('tests/Fixtures/EnumsToTest/BackendEnumWithTypoErrors.php')
        ->and($issues[2]->line)->toBe(0)
        ->and($issues[2]->misspelling->word)->toBe('enum')
        ->and($issues[2]->misspelling->suggestions)->toBe([
            'enema',
            'enemy',
            'emu',
            'anime',
        ])->and($issues[3]->file)->toEndWith('tests/Fixtures/EnumsToTest/FolderThatShouldBeIgnored/EnumWithTypoErrors.php')
        ->and($issues[3]->line)->toBe(0)
        ->and($issues[3]->misspelling->word)->toBe('enum')
        ->and($issues[3]->misspelling->suggestions)->toBe([
            'enema',
            'enemy',
            'emu',
            'anime',
        ])->and($issues[4]->file)->toEndWith('tests/Fixtures/EnumsToTest/UnitEnumWithTypoErrors.php')
        ->and($issues[4]->line)->toBe(0)
        ->and($issues[4]->misspelling->word)->toBe('enum')
        ->and($issues[4]->misspelling->suggestions)->toBe([
            'enema',
            'enemy',
            'emu',
            'anime',
        ])->and($issues[5]->file)->toEndWith('tests/Fixtures/FolderWithTypoos')
        ->and($issues[5]->line)->toBe(0)
        ->and($issues[5]->misspelling->word)->toBe('typoos')
        ->and($issues[5]->misspelling->suggestions)->toBe([
            'typos',
            'types',
            'tops',
            'poos',
        ])->and($issues[6]->file)->toEndWith('tests/Fixtures/FolderWithTypoos/FileWithTppyo.php')
        ->and($issues[6]->line)->toBe(0)
        ->and($issues[6]->misspelling->word)->toBe('tppyo')
        ->and($issues[6]->misspelling->suggestions)->toBe([
            'typo',
            'Tokyo',
            'typos',
            'topi',
        ])->and($issues[7]->file)->toEndWith('tests/Fixtures/FolderWithTypoos/FolderThatShouldBeIgnored/FileThatShoudBeIgnoredBecauseItsInsideWhitelistedFolder.php')
        ->and($issues[7]->line)->toBe(0)
        ->and($issues[7]->misspelling->word)->toBe('shoud')
        ->and($issues[7]->misspelling->suggestions)->toBe([
            'should',
            'shroud',
            'shod',
            'shout',
        ]);
});

it('detects issues in the given directory, but ignores the whitelisted directories', function (): void {
    $checker = new FileSystemChecker(
        new Config(
            whitelistedDirectories: ['FolderThatShouldBeIgnored'],
        ),
        Aspell::default(),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../Fixtures',
        'onProgress' => fn (): null => null,
    ]);

    expect($issues)->toHaveCount(3)
        ->and($issues[0]->file)->toEndWith('tests/Fixtures/FolderWithTypoos')
        ->and($issues[0]->line)->toBe(0)
        ->and($issues[0]->misspelling->word)->toBe('typoos')
        ->and($issues[0]->misspelling->suggestions)->toBe([
            'typos',
            'types',
            'tops',
            'poos',
        ])->and($issues[1]->file)->toEndWith('tests/Fixtures/FolderWithTypoos/FileThatShouldBeIgnroed.php')
        ->and($issues[1]->line)->toBe(0)
        ->and($issues[1]->misspelling->word)->toBe('ignroed')
        ->and($issues[1]->misspelling->suggestions)->toBe([
            'ignored',
            'ignores',
            'ignore',
            'inroad',
        ])->and($issues[2]->file)->toEndWith('tests/Fixtures/FolderWithTypoos/FileWithTppyo.php')
        ->and($issues[2]->line)->toBe(0)
        ->and($issues[2]->misspelling->word)->toBe('tppyo')
        ->and($issues[2]->misspelling->suggestions)->toBe([
            'typo',
            'Tokyo',
            'typos',
            'topi',
        ]);
});

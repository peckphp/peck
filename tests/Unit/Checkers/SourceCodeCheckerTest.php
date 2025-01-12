<?php

declare(strict_types=1);

use Peck\Checkers\SourceCodeChecker;
use Peck\Config;
use Peck\Plugins\Cache;
use Peck\Services\Spellcheckers\Aspell;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

it('does not detect issues in the given directory', function (): void {
    $checker = new SourceCodeChecker(
        Config::instance(),
        Aspell::default(),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../../src',
        'onSuccess' => fn (): null => null,
        'onFailure' => fn (): null => null,
    ]);

    expect($issues)->toBeEmpty();
});

it('detects issues in the given directory of classes', function (): void {
    $checker = new SourceCodeChecker(
        Config::instance(),
        Aspell::default(),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../Fixtures/ClassesToTest',
        'onSuccess' => fn (): null => null,
        'onFailure' => fn (): null => null,
    ]);

    expect($issues)->toHaveCount(18)
        ->and($issues[0]->file)->toEndWith('tests/Fixtures/ClassesToTest/ClassWithTypoErrors.php')
        ->and($issues[0]->line)->toBe(30)
        ->and($issues[0]->misspelling->word)->toBe('erorr')
        ->and($issues[0]->misspelling->suggestions)->toBe([
            'error',
            'errors',
            'Orr',
            'err',
        ])->and($issues[1]->file)->toEndWith('tests/Fixtures/ClassesToTest/ClassWithTypoErrors.php')
        ->and($issues[1]->line)->toBe(36)
        ->and($issues[1]->misspelling->word)->toBe('metohd')
        ->and($issues[1]->misspelling->suggestions)->toBe([
            'method',
            'meted',
            'mooted',
            'mated',
        ])->and($issues[2]->file)->toEndWith('tests/Fixtures/ClassesToTest/ClassWithTypoErrors.php')
        ->and($issues[2]->line)->toBe(43)
        ->and($issues[2]->misspelling->word)->toBe('erorr')
        ->and($issues[2]->misspelling->suggestions)->toBe([
            'error',
            'errors',
            'Orr',
            'err',
        ])->and($issues[3]->file)->toEndWith('tests/Fixtures/ClassesToTest/ClassWithTypoErrors.php')
        ->and($issues[3]->line)->toBe(18)
        ->and($issues[3]->misspelling->word)->toBe('properyt')
        ->and($issues[3]->misspelling->suggestions)->toBe([
            'property',
            'propriety',
            'properer',
            'properest',
        ])->and($issues[4]->file)->toEndWith('tests/Fixtures/ClassesToTest/ClassWithTypoErrors.php')
        ->and($issues[4]->line)->toBe(21)
        ->and($issues[4]->misspelling->word)->toBe('bolck')
        ->and($issues[4]->misspelling->suggestions)->toBe([
            'block',
            'bock',
            'bloc',
            'bilk',
        ])->and($issues[5]->file)->toEndWith('tests/Fixtures/ClassesToTest/ClassWithTypoErrors.php')
        ->and($issues[5]->line)->toBe(10)
        ->and($issues[5]->misspelling->word)->toBe('tst')
        ->and($issues[5]->misspelling->suggestions)->toBe([
            'test',
            'tat',
            'ST',
            'St',
        ])->and($issues[6]->file)->toEndWith('tests/Fixtures/ClassesToTest/ClassWithTypoOnConstants.php')
        ->and($issues[6]->line)->toBe(11)
        ->and($issues[6]->misspelling->word)->toBe('typoo')
        ->and($issues[6]->misspelling->suggestions)->toBe([
            'typo',
            'typos',
            'type',
            'topi',
        ])->and($issues[7]->file)->toEndWith('tests/Fixtures/ClassesToTest/ClassWithTypoOnConstants.php')
        ->and($issues[7]->line)->toBe(11)
        ->and($issues[7]->misspelling->word)->toBe('typoo')
        ->and($issues[7]->misspelling->suggestions)->toBe([
            'typo',
            'typos',
            'type',
            'topi',
        ])->and($issues[8]->file)->toEndWith('tests/Fixtures/ClassesToTest/FolderThatShouldBeIgnored/ClassWithTypoErrors.php')
        ->and($issues[8]->line)->toBe(9)
        ->and($issues[8]->misspelling->word)->toBe('properyt')
        ->and($issues[8]->misspelling->suggestions)->toBe([
            'property',
            'propriety',
            'properer',
            'properest',
        ])->and($issues[9]->file)->toEndWith('tests/Fixtures/ClassesToTest/FolderThatShouldBeIgnored/DirectoryWithNoSuggestions/ClassWithNoSuggestions.php')
        ->and($issues[9]->line)->toBe(9)
        ->and($issues[9]->misspelling->word)->toBe('supercalifragilisticexpialidociouss')
        ->and($issues[9]->misspelling->suggestions)->toBe([])

        ->and($issues[10]->file)->toEndWith('tests/Fixtures/ClassesToTest/InterfaceWithSpellingMistake.php')
        ->and($issues[10]->line)->toBe(12)
        ->and($issues[10]->misspelling->word)->toBe('erorr')
        ->and($issues[10]->misspelling->suggestions)->toBe([
            'error',
            'errors',
            'Orr',
            'err',
        ])->and($issues[11]->file)->toEndWith('tests/Fixtures/ClassesToTest/InterfaceWithSpellingMistake.php')
        ->and($issues[11]->line)->toBe(8)
        ->and($issues[11]->misspelling->word)->toBe('spellling')
        ->and($issues[11]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])->and($issues[12]->file)->toEndWith('tests/Fixtures/ClassesToTest/TraitWithTypo.php')
        ->and($issues[12]->line)->toBe(25)
        ->and($issues[12]->misspelling->word)->toBe('spellling')
        ->and($issues[12]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])->and($issues[13]->file)->toEndWith('tests/Fixtures/ClassesToTest/TraitWithTypo.php')
        ->and($issues[13]->line)->toBe(30)
        ->and($issues[13]->misspelling->word)->toBe('spellling')
        ->and($issues[13]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])->and($issues[14]->file)->toEndWith('tests/Fixtures/ClassesToTest/TraitWithTypo.php')
        ->and($issues[14]->line)->toBe(41)
        ->and($issues[14]->misspelling->word)->toBe('spellling')
        ->and($issues[14]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])->and($issues[15]->file)->toEndWith('tests/Fixtures/ClassesToTest/TraitWithTypo.php')
        ->and($issues[15]->line)->toBe(18)
        ->and($issues[15]->misspelling->word)->toBe('properyt')
        ->and($issues[15]->misspelling->suggestions)->toBe([
            'property',
            'propriety',
            'properer',
            'properest',
        ])->and($issues[16]->file)->toEndWith('tests/Fixtures/ClassesToTest/TraitWithTypo.php')
        ->and($issues[16]->line)->toBe(18)
        ->and($issues[16]->misspelling->word)->toBe('spellling')
        ->and($issues[16]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])->and($issues[17]->file)->toEndWith('tests/Fixtures/ClassesToTest/TraitWithTypo.php')
        ->and($issues[17]->line)->toBe(10)
        ->and($issues[17]->misspelling->word)->toBe('tst')
        ->and($issues[17]->misspelling->suggestions)->toBe([
            'test',
            'tat',
            'ST',
            'St',
        ]);
});

it('detects issues in the given directory of classes, but ignores the whitelisted words', function (): void {
    $config = new Config(
        whitelistedWords: ['Properyt', 'bolck'],
    );

    $checker = new SourceCodeChecker(
        $config,
        new Aspell(
            $config,
            Cache::default(),
        ),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../Fixtures/ClassesToTest',
        'onSuccess' => fn (): null => null,
        'onFailure' => fn (): null => null,
    ]);

    expect($issues)->toHaveCount(14)
        ->and($issues[0]->file)->toEndWith('tests/Fixtures/ClassesToTest/ClassWithTypoErrors.php')
        ->and($issues[0]->line)->toBe(30)
        ->and($issues[0]->misspelling->word)->toBe('erorr')
        ->and($issues[0]->misspelling->suggestions)->toBe([
            'error',
            'errors',
            'Orr',
            'err',
        ])->and($issues[1]->file)->toEndWith('tests/Fixtures/ClassesToTest/ClassWithTypoErrors.php')
        ->and($issues[1]->line)->toBe(36)
        ->and($issues[1]->misspelling->word)->toBe('metohd')
        ->and($issues[1]->misspelling->suggestions)->toBe([
            'method',
            'meted',
            'mooted',
            'mated',
        ])->and($issues[2]->file)->toEndWith('tests/Fixtures/ClassesToTest/ClassWithTypoErrors.php')
        ->and($issues[2]->line)->toBe(43)
        ->and($issues[2]->misspelling->word)->toBe('erorr')
        ->and($issues[2]->misspelling->suggestions)->toBe([
            'error',
            'errors',
            'Orr',
            'err',
        ])->and($issues[3]->file)->toEndWith('tests/Fixtures/ClassesToTest/ClassWithTypoErrors.php')
        ->and($issues[3]->line)->toBe(10)
        ->and($issues[3]->misspelling->word)->toBe('tst')
        ->and($issues[3]->misspelling->suggestions)->toBe([
            'test',
            'tat',
            'ST',
            'St',
        ])->and($issues[4]->file)->toEndWith('tests/Fixtures/ClassesToTest/ClassWithTypoOnConstants.php')
        ->and($issues[4]->line)->toBe(11)
        ->and($issues[4]->misspelling->word)->toBe('typoo')
        ->and($issues[4]->misspelling->suggestions)->toBe([
            'typo',
            'typos',
            'type',
            'topi',
        ])->and($issues[5]->file)->toEndWith('tests/Fixtures/ClassesToTest/ClassWithTypoOnConstants.php')
        ->and($issues[5]->line)->toBe(11)
        ->and($issues[5]->misspelling->word)->toBe('typoo')
        ->and($issues[5]->misspelling->suggestions)->toBe([
            'typo',
            'typos',
            'type',
            'topi',
        ])->and($issues[6]->file)->toEndWith('tests/Fixtures/ClassesToTest/FolderThatShouldBeIgnored/DirectoryWithNoSuggestions/ClassWithNoSuggestions.php')
        ->and($issues[6]->line)->toBe(9)
        ->and($issues[6]->misspelling->word)->toBe('supercalifragilisticexpialidociouss')
        ->and($issues[6]->misspelling->suggestions)->toBe([])
        ->and($issues[7]->file)->toEndWith('tests/Fixtures/ClassesToTest/InterfaceWithSpellingMistake.php')
        ->and($issues[7]->line)->toBe(12)
        ->and($issues[7]->misspelling->word)->toBe('erorr')
        ->and($issues[7]->misspelling->suggestions)->toBe([
            'error',
            'errors',
            'Orr',
            'err',
        ])->and($issues[8]->file)->toEndWith('tests/Fixtures/ClassesToTest/InterfaceWithSpellingMistake.php')
        ->and($issues[8]->line)->toBe(8)
        ->and($issues[8]->misspelling->word)->toBe('spellling')
        ->and($issues[8]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])->and($issues[9]->file)->toEndWith('tests/Fixtures/ClassesToTest/TraitWithTypo.php')
        ->and($issues[9]->line)->toBe(25)
        ->and($issues[9]->misspelling->word)->toBe('spellling')
        ->and($issues[9]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])->and($issues[10]->file)->toEndWith('tests/Fixtures/ClassesToTest/TraitWithTypo.php')
        ->and($issues[10]->line)->toBe(30)
        ->and($issues[10]->misspelling->word)->toBe('spellling')
        ->and($issues[10]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])->and($issues[11]->file)->toEndWith('tests/Fixtures/ClassesToTest/TraitWithTypo.php')
        ->and($issues[11]->line)->toBe(41)
        ->and($issues[11]->misspelling->word)->toBe('spellling')
        ->and($issues[11]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])->and($issues[12]->file)->toEndWith('tests/Fixtures/ClassesToTest/TraitWithTypo.php')
        ->and($issues[12]->line)->toBe(18)
        ->and($issues[12]->misspelling->word)->toBe('spellling')
        ->and($issues[12]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])->and($issues[13]->file)->toEndWith('tests/Fixtures/ClassesToTest/TraitWithTypo.php')
        ->and($issues[13]->line)->toBe(10)
        ->and($issues[13]->misspelling->word)->toBe('tst')
        ->and($issues[13]->misspelling->suggestions)->toBe([
            'test',
            'tat',
            'ST',
            'St',
        ]);
});

it('detects issues in the given directory of classes, but ignores the whitelisted directories', function (): void {
    $checker = new SourceCodeChecker(
        new Config(
            whitelistedPaths: ['tests/Fixtures/ClassesToTest/FolderThatShouldBeIgnored'],
        ),
        Aspell::default(),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../Fixtures/ClassesToTest',
        'onSuccess' => fn (): null => null,
        'onFailure' => fn (): null => null,
    ]);

    expect($issues)->toHaveCount(16)
        ->and($issues[0]->file)->toEndWith('tests/Fixtures/ClassesToTest/ClassWithTypoErrors.php')
        ->and($issues[0]->line)->toBe(30)
        ->and($issues[0]->misspelling->word)->toBe('erorr')
        ->and($issues[0]->misspelling->suggestions)->toBe([
            'error',
            'errors',
            'Orr',
            'err',
        ])->and($issues[1]->file)->toEndWith('tests/Fixtures/ClassesToTest/ClassWithTypoErrors.php')
        ->and($issues[1]->line)->toBe(36)
        ->and($issues[1]->misspelling->word)->toBe('metohd')
        ->and($issues[1]->misspelling->suggestions)->toBe([
            'method',
            'meted',
            'mooted',
            'mated',
        ])->and($issues[2]->file)->toEndWith('tests/Fixtures/ClassesToTest/ClassWithTypoErrors.php')
        ->and($issues[2]->line)->toBe(43)
        ->and($issues[2]->misspelling->word)->toBe('erorr')
        ->and($issues[2]->misspelling->suggestions)->toBe([
            'error',
            'errors',
            'Orr',
            'err',
        ])->and($issues[3]->file)->toEndWith('tests/Fixtures/ClassesToTest/ClassWithTypoErrors.php')
        ->and($issues[3]->line)->toBe(18)
        ->and($issues[3]->misspelling->word)->toBe('properyt')
        ->and($issues[3]->misspelling->suggestions)->toBe([
            'property',
            'propriety',
            'properer',
            'properest',
        ])->and($issues[4]->file)->toEndWith('tests/Fixtures/ClassesToTest/ClassWithTypoErrors.php')
        ->and($issues[4]->line)->toBe(21)
        ->and($issues[4]->misspelling->word)->toBe('bolck')
        ->and($issues[4]->misspelling->suggestions)->toBe([
            'block',
            'bock',
            'bloc',
            'bilk',
        ])->and($issues[5]->file)->toEndWith('tests/Fixtures/ClassesToTest/ClassWithTypoErrors.php')
        ->and($issues[5]->line)->toBe(10)
        ->and($issues[5]->misspelling->word)->toBe('tst')
        ->and($issues[5]->misspelling->suggestions)->toBe([
            'test',
            'tat',
            'ST',
            'St',
        ])->and($issues[6]->file)->toEndWith('tests/Fixtures/ClassesToTest/ClassWithTypoOnConstants.php')
        ->and($issues[6]->line)->toBe(11)
        ->and($issues[6]->misspelling->word)->toBe('typoo')
        ->and($issues[6]->misspelling->suggestions)->toBe([
            'typo',
            'typos',
            'type',
            'topi',
        ])->and($issues[7]->file)->toEndWith('tests/Fixtures/ClassesToTest/ClassWithTypoOnConstants.php')
        ->and($issues[7]->line)->toBe(11)
        ->and($issues[7]->misspelling->word)->toBe('typoo')
        ->and($issues[7]->misspelling->suggestions)->toBe([
            'typo',
            'typos',
            'type',
            'topi',
        ])->and($issues[8]->file)->toEndWith('tests/Fixtures/ClassesToTest/InterfaceWithSpellingMistake.php')
        ->and($issues[8]->line)->toBe(12)
        ->and($issues[8]->misspelling->word)->toBe('erorr')
        ->and($issues[8]->misspelling->suggestions)->toBe([
            'error',
            'errors',
            'Orr',
            'err',
        ])->and($issues[9]->file)->toEndWith('tests/Fixtures/ClassesToTest/InterfaceWithSpellingMistake.php')
        ->and($issues[9]->line)->toBe(8)
        ->and($issues[9]->misspelling->word)->toBe('spellling')
        ->and($issues[9]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])->and($issues[10]->file)->toEndWith('tests/Fixtures/ClassesToTest/TraitWithTypo.php')
        ->and($issues[10]->line)->toBe(25)
        ->and($issues[10]->misspelling->word)->toBe('spellling')
        ->and($issues[10]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])->and($issues[11]->file)->toEndWith('tests/Fixtures/ClassesToTest/TraitWithTypo.php')
        ->and($issues[11]->line)->toBe(30)
        ->and($issues[11]->misspelling->word)->toBe('spellling')
        ->and($issues[11]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])->and($issues[12]->file)->toEndWith('tests/Fixtures/ClassesToTest/TraitWithTypo.php')
        ->and($issues[12]->line)->toBe(41)
        ->and($issues[12]->misspelling->word)->toBe('spellling')
        ->and($issues[12]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])->and($issues[13]->file)->toEndWith('tests/Fixtures/ClassesToTest/TraitWithTypo.php')
        ->and($issues[13]->line)->toBe(18)
        ->and($issues[13]->misspelling->word)->toBe('properyt')
        ->and($issues[13]->misspelling->suggestions)->toBe([
            'property',
            'propriety',
            'properer',
            'properest',
        ])->and($issues[14]->file)->toEndWith('tests/Fixtures/ClassesToTest/TraitWithTypo.php')
        ->and($issues[14]->line)->toBe(18)
        ->and($issues[14]->misspelling->word)->toBe('spellling')
        ->and($issues[14]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])->and($issues[15]->file)->toEndWith('tests/Fixtures/ClassesToTest/TraitWithTypo.php')
        ->and($issues[15]->line)->toBe(10)
        ->and($issues[15]->misspelling->word)->toBe('tst')
        ->and($issues[15]->misspelling->suggestions)->toBe([
            'test',
            'tat',
            'ST',
            'St',
        ]);
});

it('handles well when it can not detect the line problem', function (): void {
    $checker = new SourceCodeChecker(
        new Config(
            whitelistedPaths: ['FolderThatShouldBeIgnored'],
        ),
        Aspell::default(),
    );

    $splFileInfo = new SplFileInfo(__FILE__, '', '');

    $line = (fn (): int => $this->getErrorLine($splFileInfo, str_repeat('a', 100)))->call($checker);

    expect($line)->toBe(0);
});

it('detects issues in the given directory of enums', function (): void {
    $checker = new SourceCodeChecker(
        Config::instance(),
        Aspell::default(),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../Fixtures/EnumsToTest',
        'onSuccess' => fn (): null => null,
        'onFailure' => fn (): null => null,
    ]);

    expect($issues)->toHaveCount(12)
        ->and($issues[0]->file)->toEndWith('tests/Fixtures/EnumsToTest/BackendEnumWithTypoErrors.php')
        ->and($issues[0]->line)->toBe(21)
        ->and($issues[0]->misspelling->word)->toBe('spellling')
        ->and($issues[0]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])
        ->and($issues[1]->file)->toEndWith('tests/Fixtures/EnumsToTest/BackendEnumWithTypoErrors.php')
        ->and($issues[1]->line)->toBe(26)
        ->and($issues[1]->misspelling->word)->toBe('spellling')
        ->and($issues[1]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])
        ->and($issues[2]->file)->toEndWith('tests/Fixtures/EnumsToTest/BackendEnumWithTypoErrors.php')
        ->and($issues[2]->line)->toBe(37)
        ->and($issues[2]->misspelling->word)->toBe('spellling')
        ->and($issues[2]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])
        ->and($issues[3]->file)->toEndWith('tests/Fixtures/EnumsToTest/BackendEnumWithTypoErrors.php')
        ->and($issues[3]->line)->toBe(13)
        ->and($issues[3]->misspelling->word)->toBe('spellling')
        ->and($issues[3]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])
        ->and($issues[4]->file)->toEndWith('tests/Fixtures/EnumsToTest/BackendEnumWithTypoErrors.php')
        ->and($issues[4]->line)->toBe(14)
        ->and($issues[4]->misspelling->word)->toBe('spellling')
        ->and($issues[4]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])
        ->and($issues[5]->file)->toEndWith('tests/Fixtures/EnumsToTest/BackendEnumWithTypoErrors.php')
        ->and($issues[5]->line)->toBe(8)
        ->and($issues[5]->misspelling->word)->toBe('spellling')
        ->and($issues[5]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])
        ->and($issues[6]->file)->toEndWith('tests/Fixtures/EnumsToTest/FolderThatShouldBeIgnored/EnumWithTypoErrors.php')
        ->and($issues[6]->line)->toBe(9)
        ->and($issues[6]->misspelling->word)->toBe('spellling')
        ->and($issues[6]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])
        ->and($issues[7]->file)->toEndWith('tests/Fixtures/EnumsToTest/UnitEnumWithTypoErrors.php')
        ->and($issues[7]->line)->toBe(20)
        ->and($issues[7]->misspelling->word)->toBe('spellling')
        ->and($issues[7]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])
        ->and($issues[8]->file)->toEndWith('tests/Fixtures/EnumsToTest/UnitEnumWithTypoErrors.php')
        ->and($issues[8]->line)->toBe(25)
        ->and($issues[8]->misspelling->word)->toBe('spellling')
        ->and($issues[8]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])
        ->and($issues[9]->file)->toEndWith('tests/Fixtures/EnumsToTest/UnitEnumWithTypoErrors.php')
        ->and($issues[9]->line)->toBe(36)
        ->and($issues[9]->misspelling->word)->toBe('spellling')
        ->and($issues[9]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])
        ->and($issues[10]->file)->toEndWith('tests/Fixtures/EnumsToTest/UnitEnumWithTypoErrors.php')
        ->and($issues[10]->line)->toBe(13)
        ->and($issues[10]->misspelling->word)->toBe('spellling')
        ->and($issues[10]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])
        ->and($issues[11]->file)->toEndWith('tests/Fixtures/EnumsToTest/UnitEnumWithTypoErrors.php')
        ->and($issues[11]->line)->toBe(8)
        ->and($issues[11]->misspelling->word)->toBe('spellling')
        ->and($issues[11]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ]);
});

it('detects issues in the given directory of enums, but ignores the whitelisted words', function (): void {
    $config = new Config(
        whitelistedWords: ['spellling'],
    );

    $checker = new SourceCodeChecker(
        $config,
        new Aspell(
            $config,
            Cache::default(),
        ),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../Fixtures/EnumsToTest',
        'onSuccess' => fn (): null => null,
        'onFailure' => fn (): null => null,
    ]);

    expect($issues)->toBeEmpty();
});

it('detects issues in the given directory of enums, but ignores the whitelisted directories', function (): void {
    $checker = new SourceCodeChecker(
        new Config(
            whitelistedPaths: ['tests/Fixtures/EnumsToTest/FolderThatShouldBeIgnored'],
        ),
        Aspell::default(),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../Fixtures/EnumsToTest',
        'onSuccess' => fn (): null => null,
        'onFailure' => fn (): null => null,
    ]);

    expect($issues)->toHaveCount(11)
        ->and($issues[0]->file)->toEndWith('tests/Fixtures/EnumsToTest/BackendEnumWithTypoErrors.php')
        ->and($issues[0]->line)->toBe(21)
        ->and($issues[0]->misspelling->word)->toBe('spellling')
        ->and($issues[0]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])
        ->and($issues[1]->file)->toEndWith('tests/Fixtures/EnumsToTest/BackendEnumWithTypoErrors.php')
        ->and($issues[1]->line)->toBe(26)
        ->and($issues[1]->misspelling->word)->toBe('spellling')
        ->and($issues[1]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])
        ->and($issues[2]->file)->toEndWith('tests/Fixtures/EnumsToTest/BackendEnumWithTypoErrors.php')
        ->and($issues[2]->line)->toBe(37)
        ->and($issues[2]->misspelling->word)->toBe('spellling')
        ->and($issues[2]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])
        ->and($issues[3]->file)->toEndWith('tests/Fixtures/EnumsToTest/BackendEnumWithTypoErrors.php')
        ->and($issues[3]->line)->toBe(13)
        ->and($issues[3]->misspelling->word)->toBe('spellling')
        ->and($issues[3]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])
        ->and($issues[4]->file)->toEndWith('tests/Fixtures/EnumsToTest/BackendEnumWithTypoErrors.php')
        ->and($issues[4]->line)->toBe(14)
        ->and($issues[4]->misspelling->word)->toBe('spellling')
        ->and($issues[4]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])
        ->and($issues[5]->file)->toEndWith('tests/Fixtures/EnumsToTest/BackendEnumWithTypoErrors.php')
        ->and($issues[5]->line)->toBe(8)
        ->and($issues[5]->misspelling->word)->toBe('spellling')
        ->and($issues[5]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])
        ->and($issues[6]->file)->toEndWith('tests/Fixtures/EnumsToTest/UnitEnumWithTypoErrors.php')
        ->and($issues[6]->line)->toBe(20)
        ->and($issues[6]->misspelling->word)->toBe('spellling')
        ->and($issues[6]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])
        ->and($issues[7]->file)->toEndWith('tests/Fixtures/EnumsToTest/UnitEnumWithTypoErrors.php')
        ->and($issues[7]->line)->toBe(25)
        ->and($issues[7]->misspelling->word)->toBe('spellling')
        ->and($issues[7]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])
        ->and($issues[8]->file)->toEndWith('tests/Fixtures/EnumsToTest/UnitEnumWithTypoErrors.php')
        ->and($issues[8]->line)->toBe(36)
        ->and($issues[8]->misspelling->word)->toBe('spellling')
        ->and($issues[8]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])
        ->and($issues[9]->file)->toEndWith('tests/Fixtures/EnumsToTest/UnitEnumWithTypoErrors.php')
        ->and($issues[9]->line)->toBe(13)
        ->and($issues[9]->misspelling->word)->toBe('spellling')
        ->and($issues[9]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ])
        ->and($issues[10]->file)->toEndWith('tests/Fixtures/EnumsToTest/UnitEnumWithTypoErrors.php')
        ->and($issues[10]->line)->toBe(8)
        ->and($issues[10]->misspelling->word)->toBe('spellling')
        ->and($issues[10]->misspelling->suggestions)->toBe([
            'spelling',
            'spilling',
            'spieling',
            'spellings',
        ]);
});

it('should never have line 0 in misspellings from SourceCodeChecker', function (): void {
    $checker = new SourceCodeChecker(
        Config::instance(),
        Aspell::default(),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../Fixtures/ClassesToTest',
        'onSuccess' => fn (): null => null,
        'onFailure' => fn (): null => null,
    ]);

    foreach ($issues as $issue) {
        expect($issue->line)->not->toBe(0);
    }
});

it('should not verify the parent class', function (): void {
    $checker = new SourceCodeChecker(Config::instance(), Aspell::default());

    $files = Finder::create()
        ->files()
        ->in(__DIR__.'/../../Fixtures/ParentAndTraits')
        ->getIterator();

    foreach ($files as $file) {
        $issues = (fn (): array => $this->getIssuesFromSourceFile($file))->call($checker);
        if ($file->getFilename() === 'ChildClass.php') {
            expect($issues)->toBeEmpty();
        } else {
            expect($issues)->toHaveCount(4);
        }
    }
});

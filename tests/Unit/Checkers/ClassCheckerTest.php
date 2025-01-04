<?php

declare(strict_types=1);

use Peck\Checkers\ClassChecker;
use Peck\Config;
use Peck\Services\Spellcheckers\InMemorySpellchecker;
use PhpSpellcheck\Spellchecker\Aspell;
use Symfony\Component\Finder\SplFileInfo;

it('does not detect issues in the given directory', function (): void {
    $checker = new ClassChecker(
        Config::instance(),
        InMemorySpellchecker::default(),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../../src',
    ]);

    expect($issues)->toBeEmpty();
});

it('detects issues in the given directory', function (): void {
    $checker = new ClassChecker(
        Config::instance(),
        InMemorySpellchecker::default(),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../Fixtures/ClassesToTest',
    ]);

    expect($issues)->toHaveCount(7)
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
        ])->and($issues[6]->file)->toEndWith('tests/Fixtures/ClassesToTest/FolderThatShouldBeIgnored/ClassWithTypoErrors.php')
        ->and($issues[6]->line)->toBe(9)
        ->and($issues[6]->misspelling->word)->toBe('properyt')
        ->and($issues[6]->misspelling->suggestions)->toBe([
            'property',
            'propriety',
            'properer',
            'properest',
        ]);
});

it('detects issues in the given directory, but ignores the whitelisted words', function (): void {
    $config = new Config(
        whitelistedWords: ['Properyt', 'bolck'],
    );

    $checker = new ClassChecker(
        $config,
        new InMemorySpellchecker(
            $config,
            Aspell::create(),
        ),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../Fixtures/ClassesToTest',
    ]);

    expect($issues)->toHaveCount(4)
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
        ]);
});

it('detects issues in the given directory, but ignores the whitelisted directories', function (): void {
    $checker = new ClassChecker(
        new Config(
            whitelistedDirectories: ['FolderThatShouldBeIgnored'],
        ),
        InMemorySpellchecker::default(),
    );

    $issues = $checker->check([
        'directory' => __DIR__.'/../../Fixtures/ClassesToTest',
    ]);

    expect($issues)->toHaveCount(6)
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
        ]);
});

it('handles well when it can not detect the line problem', function (): void {
    $checker = new ClassChecker(
        new Config(
            whitelistedDirectories: ['FolderThatShouldBeIgnored'],
        ),
        InMemorySpellchecker::default(),
    );

    $splFileInfo = new SplFileInfo(__FILE__, '', '');

    $line = (fn (): int => $this->getErrorLine($splFileInfo, str_repeat('a', 100)))->call($checker);

    expect($line)->toBe(0);
});

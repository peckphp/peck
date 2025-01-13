<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

it('may fail', function (): void {
    $process = Process::fromShellCommandline('./bin/peck check --path tests/Fixtures');

    $exitCode = $process->run();

    $output = $process->getOutput();

    preg_match('/Duration: ([0-9.]+)s/', $output, $matches);
    $output = str_replace($matches[1], '0.00', $output);

    expect($exitCode)->toBe(1)
        ->and($output)->toMatchSnapshot();
});

it('may pass', function (): void {
    $process = Process::fromShellCommandline('./bin/peck');

    $exitCode = $process->run();

    $output = $process->getOutput();

    preg_match('/Duration: ([0-9.]+)s/', $output, $matches);
    $output = str_replace($matches[1], '0.00', $output);

    expect($exitCode)->toBe(0)
        ->and($output)->toMatchSnapshot();
});

it('tests for no suggestions', function (): void {
    $process = Process::fromShellCommandline('./bin/peck check --path tests/Fixtures/ClassesToTest/FolderThatShouldBeIgnored/DirectoryWithNoSuggestions');

    $exitCode = $process->run();

    $output = $process->getOutput();
    expect($exitCode)->toBe(1)
        ->and($output)->toContain('There are no suggestions for this misspelling.');
});

it('tests multiple suggestions', function (): void {
    $process = Process::fromShellCommandline('./bin/peck check --path tests/Fixtures');

    $exitCode = $process->run();

    $output = $process->getOutput();

    expect($exitCode)->toBe(1)
        ->and($output)->toContain('Did you mean: property, propriety, properer, properest');
});

<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

it('may fail', function (): void {
    $process = Process::fromShellCommandline('./bin/peck check --path tests/Fixtures');

    $exitCode = $process->run();

    $output = removeProjectDirectory($process->getOutput());

    expect($exitCode)->toBe(1)
        ->and($output)->toMatchSnapshot();
});

it('may pass', function (): void {
    $process = Process::fromShellCommandline('./bin/peck');

    $exitCode = $process->run();

    $output = removeProjectDirectory($process->getOutput());

    expect($exitCode)->toBe(0)
        ->and($output)->toMatchSnapshot();
});

function removeProjectDirectory(string $output): string
{
    $projectDirectory = (string) realpath(__DIR__.'/../../');

    return str_replace($projectDirectory, '', $output);
}

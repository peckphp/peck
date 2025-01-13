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

it('may fail with static text option', function (): void {
    $process = Process::fromShellCommandline('./bin/peck --text="heree haas a typoo errorr"');

    $exitCode = $process->run();

    $output = $process->getOutput();

    preg_match('/Duration: ([0-9.]+)s/', $output, $matches);
    $output = str_replace($matches[1], '0.00', $output);

    expect($exitCode)->toBe(1)
        ->and($output)->toMatchSnapshot();
});

it('may pass with static text option', function (): void {
    $process = Process::fromShellCommandline('./bin/peck --text="here has a typo error"');

    $exitCode = $process->run();

    $output = $process->getOutput();

    preg_match('/Duration: ([0-9.]+)s/', $output, $matches);
    $output = str_replace($matches[1], '0.00', $output);

    expect($exitCode)->toBe(0)
        ->and($output)->toMatchSnapshot();
});

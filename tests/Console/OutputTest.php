<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

it('may fail', function (): void {
    $process = Process::fromShellCommandline('./bin/peck check --path tests/Fixtures');

    $exitCode = $process->run();

    $output = $process->getOutput();

    expect($exitCode)->toBe(1)
        ->and($output)->pipe('toMatchSnapshot', function (Closure $next) use ($output) {
            preg_match('/Duration: ([0-9.]+)s/', $output, $matches);
            if (is_string($this->value)) {
                $this->value = str_replace($matches[1], '0.00', $this->value);
            }

            return $next();
        });
});

it('may pass', function (): void {
    $process = Process::fromShellCommandline('./bin/peck');

    $exitCode = $process->run();

    $output = $process->getOutput();

    expect($exitCode)->toBe(0)
        ->and($output)->pipe('toMatchSnapshot', function (Closure $next) use ($output) {
            preg_match('/Duration: ([0-9.]+)s/', $output, $matches);
            if (is_string($this->value)) {
                $this->value = str_replace($matches[1], '0.00', $this->value);
            }

            return $next();
        });
});

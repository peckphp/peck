<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

it('may fail', function (): void {
    // Prepare
    $process = Process::fromShellCommandline('./bin/peck');

    // Act
    $exitCode = $process->run();

    // Assert
    expect($exitCode)->toBe(1)
        ->and($process->getOutput())->toContain('Did you mean:');
})->todo();

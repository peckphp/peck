<?php

declare(strict_types=1);

use Peck\Console\Commands\Cache\ClearCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

it('clears default cache directory', function (): void {
    $application = new Application;

    $application->add(new ClearCommand);

    $command = $application->find('cache:clear');

    $commandTester = new CommandTester($command);

    $commandTester->execute([]);

    $output = $commandTester->getDisplay();

    expect(trim($output))->toContain('Clearing cache...');
    expect(trim($output))->toContain('Cache successfully cleared!');
});

it('clears custom cache directory', function (): void {

    $application = new Application;

    $application->add(new ClearCommand);

    $command = $application->find('cache:clear');

    $commandTester = new CommandTester($command);

    if (! is_dir('/tmp/.peck.cache')) {
        @mkdir('/tmp/.peck.cache');
    }

    $commandTester->execute([
        'directory' => '/tmp/.peck.cache',
    ]);

    $output = $commandTester->getDisplay();

    expect(trim($output))->toContain('Clearing cache...');
    expect(trim($output))->toContain('Cache successfully cleared!');

    @unlink('/tmp/.peck.cache');
});

it('throws an exception when the specified directory does not exist', function (): void {
    $application = new Application;

    $application->add(new ClearCommand);

    $command = $application->find('cache:clear');

    $commandTester = new CommandTester($command);

    $commandTester->execute([
        'directory' => '/tmp/peck.cache',
    ]);

    $output = $commandTester->getDisplay();

    expect(trim($output))->toContain('The specified cache directory does not exist.');
});

it('only deletes cached files from custom cache directory', function (): void {
    $application = new Application;

    $application->add(new ClearCommand);

    $command = $application->find('cache:clear');

    $commandTester = new CommandTester($command);

    if (! is_dir('/tmp/custom')) {
        @mkdir('/tmp/custom');
    }

    file_put_contents('/tmp/custom/peck_1', 'test');
    file_put_contents('/tmp/custom/peck_2', 'test');
    file_put_contents('/tmp/custom/not_a_cached_file', 'test');

    $commandTester->execute([
        'directory' => '/tmp/custom',
    ]);

    expect(count(glob('/tmp/custom/*')))->toBe(1);

    @unlink('/tmp/custom');
});

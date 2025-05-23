<?php

declare(strict_types=1);

use Peck\Console\Commands\CheckCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

it('may fail', function (): void {
    $application = new Application;

    $application->add(new CheckCommand);

    $command = $application->find('check');

    $commandTester = new CommandTester($command);

    $commandTester->execute([
        '--path' => 'tests/Fixtures/ClassesToTest/FolderThatShouldBeIgnored',
    ]);

    $output = $commandTester->getDisplay();

    expect(trim($output))->toContain('Did you mean: property, propriety, properer, properest')
        ->and($commandTester->getStatusCode())->toBe(1);
});

it('may pass', function (): void {
    $application = new Application;

    $application->add(new CheckCommand);

    $command = $application->find('check');

    $commandTester = new CommandTester($command);

    $commandTester->execute([]);

    $output = $commandTester->getDisplay();

    expect(trim($output))->toContain('PASS  No misspellings found in your project.')
        ->and($commandTester->getStatusCode())->toBe(0);
});

it('may pass with lineless issues', function (): void {
    $application = new Application;

    $application->add(new CheckCommand);

    $command = $application->find('check');

    $commandTester = new CommandTester($command);

    $commandTester->execute([
        '--path' => 'tests/Fixtures/FolderWithTypoos',
    ]);

    $output = $commandTester->getDisplay();

    expect(trim($output))->toContain('Misspelling')
        ->and($commandTester->getStatusCode())->toBe(1);
});

it('may pass with init option', function (): void {
    $application = new Application;

    $application->add(new CheckCommand);

    $command = $application->find('check');

    $commandTester = new CommandTester($command);

    $commandTester->execute([
        '--init' => true,
    ]);

    $output = $commandTester->getDisplay();

    expect(trim($output))->toContain('INFO  Configuration file already exists.')
        ->and($commandTester->getStatusCode())->toBe(1);
});

it('may pass with ignore-all option', function (): void {
    $application = new Application;

    $application->add(new CheckCommand);

    $command = $application->find('check');

    $commandTester = new CommandTester($command);

    $commandTester->execute([
        '--ignore-all' => true,
    ]);

    $output = $commandTester->getDisplay();

    expect(trim($output))->toContain('PASS  No misspellings found in your project.')
        ->and($commandTester->getStatusCode())->toBe(0);

});

it('may fail with text option', function (): void {
    $application = new Application;

    $application->add(new CheckCommand);

    $command = $application->find('check');

    $commandTester = new CommandTester($command);

    $commandTester->execute([
        '--text' => 'This is a test with a typoo.',
    ]);

    $output = $commandTester->getDisplay();

    expect(trim($output))->toContain('Did you mean: typo, typos, type, topi')
        ->and($commandTester->getStatusCode())->toBe(1);
});

it('may pass with text option', function (): void {
    $application = new Application;

    $application->add(new CheckCommand);

    $command = $application->find('check');

    $commandTester = new CommandTester($command);

    $commandTester->execute([
        '--text' => 'This is a test without any typos.',
    ]);

    $output = $commandTester->getDisplay();

    expect(trim($output))->toContain('PASS  No misspellings found in the given text.')
        ->and($commandTester->getStatusCode())->toBe(0);
});

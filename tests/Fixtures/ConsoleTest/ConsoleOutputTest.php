<?php

declare(strict_types=1);

use Peck\Console\Commands\DefaultCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

it('outputs the expected string', function (): void {
    $application = new Application;

    $application->add(new DefaultCommand);

    // Taken from the setDefaultCommand() method in bin/peck
    $command = $application->find('default');

    $commandTester = new CommandTester($command);

    // We know this dir already contains an error in its only file
    $commandTester->execute([
        '--dir' => 'tests/Fixtures/ClassesToTest/FolderThatShouldBeIgnored',
    ]);

    // Get the command output
    $output = $commandTester->getDisplay();

    expect(trim($output))->toContain('Did you mean: property, propriety, properer or properest?');
});

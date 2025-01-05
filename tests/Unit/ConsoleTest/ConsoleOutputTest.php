<?php

declare(strict_types=1);

use Peck\Console\Commands\CheckCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Tester\CommandTester;

it('requires a config value when --config is passed', function (): void {
    // Find the application
    $application = new Application;
    $application->add(new CheckCommand);
    $command = $application->find('check');

    // Execute the command, but don't pass a config file
    (new CommandTester($command))->execute([
        '--config' => null,
    ]);
})->throws(InvalidOptionException::class);

it('W throw an exception when an invalid --config is passed', function (): void {
    // Find the application
    $application = new Application;
    $application->add(new CheckCommand);
    $command = $application->find('check');

    // Execute the command, but don't pass a config file
    (new CommandTester($command))->execute([
        '--config' => 'i-dont-exist',
    ]);
})->throws(InvalidOptionException::class);

it('It works when a valid --config is passed', function (): void {
    $tempConfig = 'peck2.json';
    touch($tempConfig);
    file_put_contents($tempConfig, <<<'JSON'
{
    "ignore": {
        "words": [
            "config",
            "aspell",
            "args",
            "doc",
            "bool",
            "php",
            "api"
        ],
        "directories": []
    }
}
JSON
    );

    // Act
    $output = shell_exec('./bin/peck --config='.$tempConfig);
    unlink($tempConfig);

    // Assert
    expect($output)
        ->toContain('Did you mean: name space, name-space, names pace, names-pace')
        ->not()->toContain('Did you mean: con fig, con-fig, Cong, confide');
});

<?php

declare(strict_types=1);

namespace Peck\Console\Commands;

use Composer\Autoload\ClassLoader;
use Peck\Config;
use Peck\Kernel;
use Peck\ValueObjects\Issue;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function Termwind\render;
use function Termwind\renderUsing;

/**
 * @codeCoverageIgnore
 *
 * @internal
 */
#[AsCommand(name: 'check')]
final class CheckCommand extends Command
{
    /**
     * Executes the command.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->getConfigFile($input);

        $kernel = Kernel::default();

        $issues = $kernel->handle([
            'directory' => $directory = $this->inferProjectPath(),
        ]);

        $output->writeln('');

        if ($issues === []) {
            renderUsing($output);
            render(<<<'HTML'
                <div class="mx-2 mb-1">
                    <div class="space-x-1">
                        <span class="bg-green text-white px-1 font-bold">PASS</span>
                        <span>No misspellings found in your project.</span>
                    </div>
                </div>
                HTML
            );

            return Command::SUCCESS;
        }

        foreach ($issues as $issue) {
            $this->renderIssue($output, $issue, $directory);
        }

        return Command::FAILURE;
    }

    /**
     * Configures the current command.
     */
    protected function configure(): void
    {
        $this->setDescription('Checks for misspellings in the given directory.')
            ->addOption(
                'config',
                'c',
                InputArgument::OPTIONAL | InputOption::VALUE_REQUIRED,
                'The configuration file to use.', 'peck.json'
            );
    }

    /**
     * If a user passes a config option to the command, attempt to load that file.
     *
     * If the file doesn't exist, throw an exception
     *
     * @throws InvalidOptionException
     */
    private function getConfigFile(InputInterface $input): void
    {
        $configurationPath = $input->getOption('config');

        if (! is_string($configurationPath) || ! file_exists($configurationPath)) {
            throw new InvalidOptionException('The config file you tried to load does not exist.');
        }

        Config::resolveConfigFilePathUsing(fn (): mixed => $configurationPath);
    }

    /**
     * Infer the project's base directory from the environment.
     */
    private function inferProjectPath(): string
    {
        $basePath = dirname(array_keys(ClassLoader::getRegisteredLoaders())[0]);

        return match (true) {
            isset($_ENV['APP_BASE_PATH']) => $_ENV['APP_BASE_PATH'],
            default => match (true) {
                is_dir($basePath.'/src') => ($basePath.'/src'),
                is_dir($basePath.'/app') => ($basePath.'/app'),
                default => $basePath,
            },
        };
    }

    private function renderIssue(OutputInterface $output, Issue $issue, string $currentDirectory): void
    {
        renderUsing($output);

        $file = str_replace($currentDirectory, '.', $issue->file);
        $lineInfo = ($issue->line !== 0) ? ":{$issue->line}" : '';
        $suggestions = implode(', ', $issue->misspelling->suggestions);

        render(<<<HTML
            <div class="mx-2 mb-1">
                <div class="space-x-1">
                    <span class="bg-red text-white px-1 font-bold">ISSUE</span>
                    <span>Misspelling in <strong><a href="{$issue->file}{$lineInfo}">{$file}{$lineInfo}</a></strong>: '<strong>{$issue->misspelling->word}</strong>'</span>
                </div>

                <div class="space-x-1 text-gray-700">
                    <span>Did you mean:</span>
                    <span class="font-bold">{$suggestions}</span>
                </div>
            </div>
        HTML);
    }
}

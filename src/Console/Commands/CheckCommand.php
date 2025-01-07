<?php

declare(strict_types=1);

namespace Peck\Console\Commands;

use Composer\Autoload\ClassLoader;
use Exception;
use Peck\Config;
use Peck\Kernel;
use Peck\ValueObjects\Issue;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function Termwind\render;
use function Termwind\renderUsing;

/**
 * @internal
 */
#[AsCommand(name: 'check')]
final class CheckCommand extends Command
{
    /**
     * @var array<string, array<int, array<string, int>>>
     */
    private array $lastColumn = []; // 1MB, 2MB, 4

    /**
     * Executes the command.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        renderUsing($output);

        $configurationPath = $input->getOption('config');
        Config::resolveConfigFilePathUsing(fn (): mixed => $configurationPath);

        $kernel = Kernel::default();

        $issues = $kernel->handle([
            'directory' => $directory = $this->findPathToScan($input),
        ]);

        $output->writeln('');

        if ($issues === []) {
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
            match ($issue->line > 0) {
                true => $this->renderLineIssue($issue, $directory),
                default => $this->renderLineLessIssue($issue, $directory),
            };
        }

        return Command::FAILURE;
    }

    /**
     * Configures the current command.
     */
    protected function configure(): void
    {
        $this->setDescription('Checks for misspellings in the given directory.')
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'The configuration file to use.', 'peck.json')
            ->addOption(
                'path',
                'p',
                InputArgument::OPTIONAL | InputOption::VALUE_REQUIRED,
                'The path to check for misspellings.'
            );
    }

    /**
     * Decides whether to use a passed directory, or figure out the directory to scan automatically
     */
    private function findPathToScan(InputInterface $input): string
    {
        $passedDirectory = $input->getOption('path');

        if (! is_string($passedDirectory)) {
            return $this->inferProjectPath();
        }

        return $passedDirectory;
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

    /**
     * Render the issue with the line.
     */
    private function renderLineIssue(Issue $issue, string $currentDirectory): void
    {
        $lines = file($issue->file);
        $lineContent = $lines[$issue->line - 1] ?? '';

        $column = $this->getIssueColumn($issue, $lineContent);
        $this->lastColumn[$issue->file][$issue->line][$issue->misspelling->word] = $column;

        $lineInfo = ":{$issue->line}:$column";

        $alignSpacer = str_repeat(' ', 6);
        $spacer = str_repeat('-', $column);

        $suggestions = $this->formatIssueSuggestionsForDisplay(
            $issue,
            strtolower($lineContent[$column]) !== $lineContent[$column],
        );

        $relativePath = str_replace($currentDirectory, '.', $issue->file);

        render(<<<HTML
            <div class="mx-2 mb-2">
                <div class="space-x-1">
                    <span class="bg-red text-white px-1 font-bold">ISSUE</span>
                    <span>Misspelling in <strong><a href="{$issue->file}{$lineInfo}">{$relativePath}{$lineInfo}</a></strong>: '<strong>{$issue->misspelling->word}</strong>'</span>
                    <code start-line="{$issue->line}">{$lineContent}</code>
                    <pre class="text-red-500 font-bold">{$alignSpacer}{$spacer}^</pre>
                </div>

                <div class="space-x-1 text-gray-700">
                    <span>Did you mean:</span>
                    <span class="font-bold">{$suggestions}</span>
                </div>
            </div>
        HTML);
    }

    /**
     * Render the issue without the line.
     */
    private function renderLineLessIssue(Issue $issue, string $currentDirectory): void
    {
        $column = $this->getIssueColumn($issue, $issue->file);
        $this->lastColumn[$issue->file][$issue->line][$issue->misspelling->word] = $column;

        $spacer = str_repeat('-', $column);

        $suggestions = $this->formatIssueSuggestionsForDisplay(
            $issue,
            strtolower($issue->file[$column]) !== $issue->file[$column],
        );

        $relativePath = str_replace($currentDirectory, '.', $issue->file);

        render(<<<HTML
            <div class="mx-2 mb-2">
                <div class="space-x-1">
                    <span class="bg-red text-white px-1 font-bold">ISSUE</span>
                    <span>Misspelling in <strong><a href="{$issue->file}">{$relativePath}</a></strong>: '<strong>{$issue->misspelling->word}</strong>'</span>
                    <pre class="text-blue-300 font-bold">{$issue->file}</pre>
                    <pre class="text-red-500 font-bold">{$spacer}^</pre>
                </div>

                <div class="space-x-1 text-gray-700">
                    <span>Did you mean:</span>
                    <span class="font-bold">{$suggestions}</span>
                </div>
            </div>
        HTML);
    }

    /**
     * Format the issue suggestions.
     */
    private function formatIssueSuggestionsForDisplay(Issue $issue, bool $capitalized): string
    {
        $suggestions = $issue->misspelling->suggestions;

        if ($capitalized) {
            $suggestions = array_map('ucfirst', $suggestions);
        }

        return implode(', ', $suggestions);
    }

    /**
     * Get the column of the issue in the line.
     */
    private function getIssueColumn(Issue $issue, string $lineContent): int
    {
        $fromColumn = isset($this->lastColumn[$issue->file][$issue->line][$issue->misspelling->word]) ? $this->lastColumn[$issue->file][$issue->line][$issue->misspelling->word] + 1 : 0;
        $projectDirectory = (string) realpath(__DIR__.'/../../../');
        $lineContent = str_replace(strtolower($projectDirectory), '', strtolower($lineContent));
        $column = strpos($lineContent, $issue->misspelling->word, $fromColumn);

        if ($column === false) {
            throw (new Exception("Could not find the misspelling '{$issue->misspelling->word}' in the line '{$lineContent}'"));
        }

        return $column;
    }
}

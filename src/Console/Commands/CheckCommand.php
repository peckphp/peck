<?php

declare(strict_types=1);

namespace Peck\Console\Commands;

use Composer\Autoload\ClassLoader;
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
    private array $lastColumn = [];

    /**
     * Executes the command.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $start = microtime(true);

        renderUsing($output);

        if ($input->getOption('init')) {
            return $this->initConfiguration();
        }

        $configurationPath = $input->getOption('config');
        Config::resolveConfigFilePathUsing(fn (): mixed => $configurationPath);

        $kernel = Kernel::default();

        $output->write('  ');

        $issues = $kernel->handle([
            'directory' => $this->findPathToScan($input),
            'onSuccess' => fn () => $output->write('<fg=gray>.</>'),
            'onFailure' => fn () => $output->write('<fg=red;options=bold>⨯</>'),
        ]);

        $output->writeln('');
        $output->writeln('');

        if ($issues === []) {
            render(<<<HTML
                <div class="mx-2 mb-1">
                    <div class="space-x-1 mb-1">
                        <span class="bg-green text-white px-1 font-bold">PASS</span>
                        <span>No misspellings found in your project.</span>
                    </div>
                    <div>
                        <span class="font-bold text-gray-600">Duration:</span> {$this->getDuration($start)}s
                    </div>
                </div>
                HTML
            );

            return Command::SUCCESS;
        }

        foreach ($issues as $issue) {
            match ($issue->line > 0) {
                true => $this->renderLineIssue($issue),
                default => $this->renderLineLessIssue($issue),
            };
        }

        render(<<<HTML
            <div class="mx-2 mb-1">
                <span class="font-bold">Duration:</span> {$this->getDuration($start)}s
            </div>
            HTML
        );

        return Command::FAILURE;
    }

    /**
     * Configures the current command.
     */
    protected function configure(): void
    {
        $this->setDescription('Checks for misspellings in the given directory.')
            ->addOption('init', 'i', InputOption::VALUE_NONE, 'Initialize a new configuration file.')
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
                is_dir($basePath.'/app') => ($basePath.'/app'),
                is_dir($basePath.'/src') => ($basePath.'/src'),
                default => $basePath,
            },
        };
    }

    /**
     * Render the issue with the line.
     */
    private function renderLineIssue(Issue $issue): void
    {
        $relativePath = str_replace((string) getcwd(), '.', $issue->file);

        $lines = file($issue->file);
        $lineContent = $lines[$issue->line - 1] ?? '';

        $column = $this->getIssueColumn($issue, $lineContent);
        $this->lastColumn[$issue->file][$issue->line][$issue->misspelling->word] = $column;

        $lineInfo = ":{$issue->line}:$column";

        $alignSpacer = str_repeat(' ', 6);
        $spacer = str_repeat('-', $column);

        $suggestions = $this->formatIssueSuggestionsForDisplay(
            $issue,
        );

        render(<<<HTML
            <div class="mx-2 mb-1">
                <div class="space-x-1">
                    <span class="bg-red text-white px-1 font-bold">Misspelling</span>
                    <span><strong><a href="{$issue->file}{$lineInfo}">{$relativePath}{$lineInfo}</a></strong>: '<strong>{$issue->misspelling->word}</strong>'</span>
                    <code start-line="{$issue->line}">{$lineContent}</code>
                    <pre class="text-red-500 font-bold">{$alignSpacer}{$spacer}^</pre>
                </div>

                <div class="space-x-1 text-gray-700">
                    <span>Did you mean:</span>
                    <span class="font-bold">{$suggestions}</span>
                </div>
            </div>
        HTML
        );
    }

    /*
     * Initialize the configuration file.
     */
    private function initConfiguration(): int
    {
        if (! Config::init()) {
            render(<<<'HTML'
                <div class="mx-2 mb-1">
                    <div class="space-x-1">
                        <span class="bg-blue text-white px-1 font-bold">INFO</span>
                        <span>Configuration file already exists.</span>
                    </div>
                </div>
                HTML,
            );

            return Command::FAILURE;
        }

        render(<<<'HTML'
            <div class="mt-1">
                <div class="mx-2 mb-1">
                    <div class="space-x-1">
                        <span class="bg-green text-white px-1 font-bold">SUCCESS</span>
                        <span>Configuration file has been created.</span>
                    </div>
                </div>
                <div class="mx-2 mb-1">
                    <span>Now you can specify the words or directories to ignore in <strong>[peck.json]</strong>.</span>
                </div>
                <div class="mx-2 mb-1">
                    <span>Then run <strong>[./vendor/bin/peck]</strong> to check your project for spelling mistakes.</span>
                </div>
            </div>
            HTML
        );

        return Command::SUCCESS;
    }

    /**
     * Render the issue without the line.
     */
    private function renderLineLessIssue(Issue $issue): void
    {
        $relativePath = str_replace((string) getcwd(), '.', $issue->file);

        $column = $this->getIssueColumn($issue, $relativePath);
        $this->lastColumn[$issue->file][$issue->line][$issue->misspelling->word] = $column;

        $spacer = str_repeat('-', $column);

        $suggestions = $this->formatIssueSuggestionsForDisplay(
            $issue,
        );

        render(<<<HTML
            <div class="mx-2 mb-1">
                <div class="space-x-1">
                    <span class="bg-red text-white px-1 font-bold">Misspelling</span>
                    <span><strong><a href="{$issue->file}">{$relativePath}</a></strong>: '<strong>{$issue->misspelling->word}</strong>'</span>
                    <pre class="text-blue-300 font-bold">{$relativePath}</pre>
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
    private function formatIssueSuggestionsForDisplay(Issue $issue): string
    {
        $suggestions = array_map(
            'strtolower',
            $issue->misspelling->suggestions,
        );

        return implode(', ', $suggestions);
    }

    /**
     * Get the column of the issue in the line.
     */
    private function getIssueColumn(Issue $issue, string $lineContent): int
    {
        $fromColumn = isset($this->lastColumn[$issue->file][$issue->line][$issue->misspelling->word])
            ? $this->lastColumn[$issue->file][$issue->line][$issue->misspelling->word] + 1 : 0;

        return (int) strpos(strtolower($lineContent), $issue->misspelling->word, $fromColumn);
    }

    private function getDuration(float $startTime): string
    {
        return number_format(microtime(true) - $startTime, 2);
    }
}

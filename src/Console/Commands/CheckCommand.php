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
     * @var array<string, array<int, array<string, int>>>
     */
    private array $lastColumn = [];

    /**
     * Executes the command.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configurationPath = $input->getOption('config');
        Config::resolveConfigFilePathUsing(fn (): mixed => $configurationPath);

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
            if ($issue->line === 0) {
                $this->renderPathIssue($output, $issue, $directory);

                continue;
            }
            $this->renderFileIssue($output, $issue, $directory);
        }

        return Command::FAILURE;
    }

    /**
     * Configures the current command.
     */
    protected function configure(): void
    {
        $this->setDescription('Checks for misspellings in the given directory.')
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'The configuration file to use.', 'peck.json');
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

    private function renderFileIssue(OutputInterface $output, Issue $issue, string $currentDirectory): void
    {
        renderUsing($output);

        $lines = file($issue->file);
        $lineContent = $lines[$issue->line - 1] ?? '';

        $column = $this->getIssueColumn($issue, $lineContent);
        $this->lastColumn[$issue->file][$issue->line][$issue->misspelling->word] = $column;

        $lineInfo = ":{$issue->line}:$column";

        // termwind "<code>" adds some spaces to the left, plus the space-x-1 of the wrapper div
        $alignSpacer = str_repeat(' ', 6);
        $spacer = str_repeat('-', $column);

        $capitalized = strtolower($lineContent[$column]) !== $lineContent[$column];

        $suggestions = $issue->misspelling->suggestions;
        if ($capitalized) {
            $suggestions = array_map('ucfirst', $suggestions);
        }
        $suggestions = implode(', ', $suggestions);

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

    private function renderPathIssue(OutputInterface $output, Issue $issue, string $currentDirectory): void
    {
        renderUsing($output);

        $column = $this->getIssueColumn($issue, $issue->file);
        $this->lastColumn[$issue->file][$issue->line][$issue->misspelling->word] = $column;

        // termwind "<code>" adds some spaces to the left, plus the space-x-1 of the wrapper div
        $spacer = str_repeat('-', $column);

        $capitalized = strtolower($issue->file[$column]) !== $issue->file[$column];

        $suggestions = $issue->misspelling->suggestions;
        if ($capitalized) {
            $suggestions = array_map('ucfirst', $suggestions);
        }
        $suggestions = implode(', ', $suggestions);

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

    private function getIssueColumn(Issue $issue, string $lineContent): int
    {
        $fromColumn = isset($this->lastColumn[$issue->file][$issue->line][$issue->misspelling->word]) ? $this->lastColumn[$issue->file][$issue->line][$issue->misspelling->word] + 1 : 0;
        $column = strpos(strtolower($lineContent), $issue->misspelling->word, $fromColumn);

        if ($column === false) {
            throw (new Exception("Could not find the misspelling '{$issue->misspelling->word}' in the line '{$lineContent}'"));
        }

        return $column;
    }
}

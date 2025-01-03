<?php

declare(strict_types=1);

namespace Peck\Console\Commands;

use Composer\Autoload\ClassLoader;
use Exception;
use Peck\Kernel;
use Peck\ValueObjects\Issue;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function Termwind\render;
use function Termwind\renderUsing;

/**
 * @codeCoverageIgnore
 *
 * @internal
 */
#[AsCommand(name: 'default')]
final class DefaultCommand extends Command
{
    /**
     * Executes the command.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
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
        $this->setDescription('Checks for misspellings in the given directory.');
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

        $fullPath = $issue->file;
        $relativePath = str_replace($currentDirectory, '.', $fullPath);

        $column = 0;

        $lineDetails = '';
        $lineInfo = '';

        $suggestions = $issue->misspelling->suggestions;

        static $lastColumn = [];

        if ($issue->line > 0) {
            // its a file, work with contents
            $lines = file($fullPath);

            $lineContent = $lines[$issue->line - 1] ?? '';
            if ($lineContent === '') {
                throw (new Exception("Could not read the line {$issue->line} in the file '{$fullPath}'"));
            }

            $fromColumn = isset($lastColumn[$fullPath][$issue->line][$issue->misspelling->word]) ? $lastColumn[$fullPath][$issue->line][$issue->misspelling->word] + 1 : 0;
            $column = strpos(strtolower($lineContent), $issue->misspelling->word, $fromColumn);
            if ($column === 0 || $column === false) {
                throw (new Exception("Could not find the misspelling '{$issue->misspelling->word}' in the line '{$lineContent}'"));
            }

            $lineInfo = ":{$issue->line}:$column";

            $capitalized = strtolower($lineContent[$column]) !== $lineContent[$column];

            // termwind "<code>" adds some spaces to the left, plus the space-x-1 of the wrapper div
            $align_spacer = str_repeat(' ', 6);
            $spacer = str_repeat('-', $column);

            $lastColumn[$fullPath][$issue->line][$issue->misspelling->word] = $column;

            $lineDetails = <<<HTML
                <code start-line="{$issue->line}">{$lineContent}</code>
                <pre class="text-red-500 font-bold">{$align_spacer}{$spacer}^</pre>
            HTML;
        } else {
            // it's a path (directory or file)
            $fromColumn = isset($lastColumn[$fullPath][$issue->line][$issue->misspelling->word]) ? $lastColumn[$fullPath][$issue->line][$issue->misspelling->word] + 1 : 0;
            $column = strpos(strtolower($fullPath), $issue->misspelling->word, $fromColumn);
            if ($column === 0 || $column === false) {
                throw (new Exception("Could not find the misspelling '{$issue->misspelling->word}' in the path '{$fullPath}'"));
            }

            $capitalized = strtolower($fullPath[$column]) !== $fullPath[$column];

            // termwind "<code>" adds some spaces to the left, plus the space-x-1 of the wrapper div
            $spacer = str_repeat('-', $column);

            $lastColumn[$fullPath][$issue->line][$issue->misspelling->word] = $column;

            $lineDetails = <<<HTML
                <pre class="text-blue-300 font-bold">{$fullPath}</pre>
                <pre class="text-red-500 font-bold">{$spacer}^</pre>
            HTML;
        }

        if ($capitalized) {
            $suggestions = array_map('ucfirst', $suggestions);
        }

        $suggestions = implode(', ', $issue->misspelling->suggestions);

        render(<<<HTML
            <div class="mx-2 mb-2">
                <div class="space-x-1">
                    <span class="bg-red text-white px-1 font-bold">ISSUE</span>
                    <span>Misspelling in <strong><a href="{$fullPath}{$lineInfo}">{$relativePath}{$lineInfo}</a></strong>: '<strong>{$issue->misspelling->word}</strong>'</span>
                    {$lineDetails}
                </div>

                <div class="space-x-1 text-gray-700">
                    <span>Did you mean:</span>
                    <span class="font-bold">{$suggestions}</span>
                </div>
            </div>
        HTML);
    }
}

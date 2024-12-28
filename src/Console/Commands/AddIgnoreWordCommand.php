<?php

declare(strict_types=1);

namespace Peck\Console\Commands;

use Peck\Config;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @codeCoverageIgnore
 *
 * @internal
 */
#[AsCommand(name: 'ignore')]
class AddIgnoreWordCommand extends Command
{
    /**
     * Executes the command.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $wordInput */
        $wordInput = $input->getArgument('word');
        $word = strtolower($wordInput);

        $config = Config::instance();

        if (in_array($word, $config->whitelistedWords, true)) {
            $output->writeln('');
            $output->writeln("<info>The word '<error>{$wordInput}</error>' is already in the ignore words list.</info>");

            return Command::FAILURE;
        }

        $config->set('ignore.words', array_merge($config->whitelistedWords, [$word]));

        $output->writeln("<info>Successfully added '{$wordInput}' to the ignore words list</info>");

        return Command::SUCCESS;
    }

    /**
     * Configures the current command.
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Add a word to the ignore list config for the spell check process to ignore it.')
            ->addArgument(
                name: 'word',
                mode: InputArgument::REQUIRED,
                description: 'The word to whitelist'
            );
    }
}

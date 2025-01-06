<?php

declare(strict_types=1);

namespace Peck\Console\Commands\Cache;

use Exception;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
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
#[AsCommand(
    name: 'cache:clear',
    description: 'Clears cached data.'
)]
final class ClearCommand extends Command
{
    /**
     * Configures the command.
     */
    protected function configure(): void
    {
        $this->addArgument('namespace', InputArgument::OPTIONAL, 'Cache namespace to clear.', '');
    }

    /**
     * Executes the command.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Clearing cache...</info>');

        $namespace = $input->getArgument('namespace');

        try {
            (new FilesystemAdapter(is_string($namespace) ? $namespace : ''))->clear();
            $output->writeln('<info>Cache successfully cleared!</info>');

            return Command::SUCCESS;
        } catch (Exception $e) {
            $output->writeln('<error>Failed to clear cache: '.$e->getMessage().'</error>');

            return Command::FAILURE;
        }
    }
}

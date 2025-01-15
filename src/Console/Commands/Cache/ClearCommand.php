<?php

declare(strict_types=1);

namespace Peck\Console\Commands\Cache;

use Exception;
use Peck\Plugins\Cache;
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
#[AsCommand(name: 'cache:clear', description: 'Clears cached data.')]
final class ClearCommand extends Command
{
    /**
     * Configures the command.
     */
    protected function configure(): void
    {
        $this->addArgument('directory', InputArgument::OPTIONAL, 'Cache directory', null);
    }

    /**
     * Executes the command.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Clearing cache...</info>');

        $directory = $input->getArgument('directory');

        try {

            if (is_string($directory) && ! is_dir($directory)) {
                throw new Exception('The specified cache directory does not exist.');
            }

            match (is_string($directory)) {
                true => Cache::create($directory)->flush(),
                default => Cache::default()->flush(),
            };

            $output->writeln('<info>Cache successfully cleared!</info>');

            return Command::SUCCESS;
        } catch (Exception $e) {
            $output->writeln('<error>Failed to clear cache: '.$e->getMessage().'</error>');

            return Command::FAILURE;
        }
    }
}

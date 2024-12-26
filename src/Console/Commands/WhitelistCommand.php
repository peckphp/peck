<?php

declare(strict_types=1);

namespace Peck\Console\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Peck\Services\WhitelistManager;

/**
 * @internal
 */
#[AsCommand(name: 'whitelist:add')]
class WhitelistCommand extends Command
{
    public function __construct(
        private readonly WhitelistManager $whitelistManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Add a word to the spelling whitelist')
            ->addArgument(
                'word',
                InputArgument::REQUIRED,
                'The word to whitelist'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $word = strtolower($input->getArgument('word'));

        $this->whitelistManager->add($word);

        $output->writeln("<info>Successfully added '{$word}' to whitelist</info>");

        return Command::SUCCESS;
    }
}
<?php

namespace AlphaSoft\AsLinkOrm\Command;

use AlphaSoft\AsLinkOrm\EntityManager;
use AlphaSoft\AsLinkOrm\Platform\PlatformInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('aslink:database:drop', 'Drop the SQL database')]
class DatabaseDropCommand extends Command
{
    public function __construct(private readonly PlatformInterface $platform, private $env = null)
    {
        parent::__construct(null);
    }

    public function configure(): void
    {
        $this
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force the database drop')
            ->setHelp('This command allows you to drop the SQL database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->isEnabled()) {
            $io->error('This command is only available in `dev` environment.');
            return Command::FAILURE;
        }

        if (!$input->getOption('force')) {
            $io->error('You must use the --force option to drop the database.');
            return Command::FAILURE;
        }

        $this->platform->dropDatabase();
        $io->success('The SQL database has been successfully dropped.');

        return Command::SUCCESS;
    }

    public function isEnabled(): bool
    {
        return 'dev' === $this->env;
    }
}

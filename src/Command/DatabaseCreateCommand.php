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

#[AsCommand('aslink:database:create', 'Create a new SQL database')]
class DatabaseCreateCommand extends Command
{
    public function __construct(private readonly PlatformInterface $platform)
    {
        parent::__construct(null);
    }

    public function configure(): void
    {
        $this
            ->addOption('if-not-exists', null, InputOption::VALUE_NONE, 'Create the SQL database only if it does not already exist')
            ->setHelp('This command allows you to create the SQL database. Use the --if-not-exists option to create it only if it does not already exist.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $platform = $this->platform;
        if ($input->getOption('if-not-exists') === true) {
            $platform->createDatabaseIfNotExists();
            $io->info(sprintf('The SQL database "%s" has been successfully created (if it did not already exist).', $platform->getDatabaseName()));
        } else {
            $platform->createDatabase();
            $io->success(sprintf('The SQL database "%s" has been successfully created.',$platform->getDatabaseName()));
        }

        return Command::SUCCESS;
    }
}

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
use Symfony\Component\Console\Helper\Table;

#[AsCommand('aslink:show:columns', 'Show the list of columns table in the SQL database')]
class ShowColumnsTableCommand extends Command
{
    public function __construct(private readonly PlatformInterface $platform)
    {
        parent::__construct(null);
    }

    public function configure(): void
    {
        $this
            ->addArgument("table", InputOption::VALUE_REQUIRED, 'The table name')
            ->setHelp('This command allows you to show the list of columns table in the SQL database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $tableName = $input->getArgument("table");
        if ($tableName === null) {
            $io->error("Table name is required");
            return Command::FAILURE;
        }

        $platform = $this->platform;
        $columns = $platform->listTableColumns($tableName);
        if ($columns === []) {
            $io->info('Columns not found in table : ' . $tableName);
            return Command::SUCCESS;
        }

        $table = new Table($output);
        $table
            ->setHeaders(array_keys($columns[0]))
            ->setRows($columns);

        $io->section('Database : ' . $platform->getDatabaseName());
        $io->info('Table : ' . $tableName);

        $table->render();
        return Command::SUCCESS;
    }
}

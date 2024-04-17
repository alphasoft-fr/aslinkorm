<?php

namespace AlphaSoft\AsLinkOrm\Command;

use AlphaSoft\AsLinkOrm\AsLinkConnection;
use AlphaSoft\AsLinkOrm\EntityManager;
use AlphaSoft\AsLinkOrm\Platform\PlatformInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('aslink:database:search', 'Search for occurrences of a keyword in all tables of the database and display the results.')]
class DatabaseSearchCommand extends Command
{
    public function __construct(private readonly EntityManager $entityManager)
    {
        parent::__construct(null);
    }

    public function configure(): void
    {
        $this
            ->addArgument('keyword', InputArgument::REQUIRED, 'The keyword to search for')
            ->setHelp('This command allows you to search for occurrences of a keyword in all tables of the database and display the results.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $keyword = $input->getArgument('keyword');

        $platform = $this->entityManager->createDatabasePlatform();
        $connection = $this->entityManager->getConnection();
        $io->section('Database : ' . $platform->getDataBaseName());
        foreach ($platform->listTables() as $table) {
            $columns = $platform->listTableColumns($table);
            $wheres = [];
            foreach ($columns as $column) {
                $wheres[] = sprintf('`%s` LIKE "%s"',$column['name'], "%$keyword%");
            }
            $data = $connection->executeQuery(sprintf('SELECT * from %s WHERE %s', $table, implode(' OR ', $wheres)))->fetchAllAssociative();
            if ($data === []) {
                continue;
            }

            $io->info('Table : ' . $table);
            $table = new Table($output);
            $table
                ->setHeaders(array_keys($data[0]))
                ->setRows($data);

            $table
                ->setVertical()
                ->render();
        }

        return Command::SUCCESS;
    }
}

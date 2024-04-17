<?php

namespace AlphaSoft\AsLinkOrm\Command;

use AlphaSoft\AsLinkOrm\AsLinkConnection;
use AlphaSoft\AsLinkOrm\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\Table;

#[AsCommand('aslink:execute:query', 'Execute a SQL query')]
class ExecuteQueryCommand extends Command
{
    public function __construct(private readonly EntityManager $entityManager)
    {
        parent::__construct(null);
    }

    public function configure(): void
    {
        $this
            ->addArgument("query", InputOption::VALUE_REQUIRED, 'The SQL query')
            ->setHelp('This command allows you to execute a SQL query.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $query = $input->getArgument("query");
        if ($query === null) {
            $io->error("SQL query is required");
            return Command::FAILURE;
        }

        $data = $this->entityManager->getConnection()->executeQuery($query)->fetchAllAssociative();
        if ($data === []) {
            $io->info('The query yielded an empty result set.');
            return Command::SUCCESS;
        }

        $io->section('Database : ' . $this->entityManager->getConnection()->getDataBaseName());
        $table = new Table($output);
        $table
            ->setHeaders(array_keys($data[0]))
            ->setRows($data);

        $table->render();
        return Command::SUCCESS;
    }
}

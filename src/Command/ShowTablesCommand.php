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

#[AsCommand('aslink:show:tables', 'Show the list of tables in the SQL database')]
class ShowTablesCommand extends Command
{
    public function __construct(private readonly PlatformInterface $platform)
    {
        parent::__construct(null);
    }

    public function configure(): void
    {
        $this->setHelp('This command allows you to show the list of tables in the SQL database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $platform = $this->platform;

        $table = new Table($output);
        $table
            ->setHeaders(['Tables'])
            ->setRows(array_map(function (string $table) {
                return [$table];
            }, $platform->listTables()));

        $io->section('Database : ' . $platform->getDatabaseName());
        $table->render();
        return Command::SUCCESS;
    }
}

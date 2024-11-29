<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Bundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TheCodingMachine\GraphQLite\Bundle\Manager\SchemaManager;
use TheCodingMachine\GraphQLite\Bundle\Utils\SchemaPrinterForGraphQLite;
use TheCodingMachine\GraphQLite\Schema;

/**
 * Shamelessly stolen from Api Platform
 */
#[AsCommand(
    name: 'graphqlite:dump-schema',
    description: 'Export the GraphQL schema in Schema Definition Language (SDL)'
)]
class DumpSchemaCommand extends Command
{
    public function __construct(protected readonly SchemaManager $schemaManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('schema', InputArgument::OPTIONAL, 'Schema name', 'default')
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Write output to file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $filename = $input->getOption('output');
        $schemaName = (string) $input->getArgument('schema');

        $schema = $this->schemaManager->getSchemaByNamespace($schemaName);

        $schemaExport = SchemaPrinterForGraphQLite::doPrint($schema, ['sortTypes' => true]);

        if (is_string($filename)) {
            file_put_contents($filename, $schemaExport);
            $io->success(sprintf('Data written to %s.', $filename));
        } else {
            $output->writeln($schemaExport);
        }

        return Command::SUCCESS;
    }
}

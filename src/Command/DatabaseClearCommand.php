<?php

namespace App\Command;

use App\Entity\Task;
use App\Entity\TaskList;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'database:clear',
    description: 'Clean up database',
)]
class DatabaseClearCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }


    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Which table should be cleared')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1 == 'all') {
            $this->truncateEntities([
                User::class,
                Task::class,
                TaskList::class
            ]);
        }

        if ($arg1 == 'user') {
            $this->truncateEntities([
                User::class
            ]);
        }

        if ($arg1 == 'task') {
            $this->truncateEntities([
                Task::class
            ]);
        }

        if ($arg1 == 'list') {
            $this->truncateEntities([
                TaskList::class
            ]);
        }


        $io->success('You have already clean up database table '.$arg1.' .');

        return Command::SUCCESS;
    }

    private function truncateEntities(array  $entities)
    {
        $connection = $this->entityManager->getConnection();
        $databasePlatform = $connection->getDatabasePlatform();
        if ($databasePlatform->supportsForeignKeyConstraints()){
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS=0');
        }
        foreach ($entities as $entity){
            $query = $databasePlatform->getTruncateTableSQL(
                $this->entityManager->getClassMetadata($entity)->getTableName()
            );
            $connection->executeQuery($query);
        }
        if ($databasePlatform->supportsForeignKeyConstraints()){
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}

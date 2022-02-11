<?php

namespace App\Controller;

use App\Entity\TaskList;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TasksController extends AbstractController
{
    #[Route('/tasks/{userId}/{listId}', name: 'tasks_show',methods: ['GET'])]
    public function tasksShow(EntityManagerInterface $entityManager,int $userId,int $listId): Response
    {
        $response = new Response();
        $list = $entityManager->getRepository(TaskList::class)->find($listId);

        /**@var TaskList $list**/
        if ($list->getUser()->getId() !== $userId)
        {
            throw new \Exception('Not have permission to access this list.',403);
        }
        $tasks = $list->getTasks();

        if (!$tasks)
        {
            return $this->json([]);
        }
        $resultArray = array();
        foreach ( $tasks as $key => $task )
        {
            $resultArray[$key]['taskId'] = $task->getId();
            $resultArray[$key]['name'] = $task->getName();
            $resultArray[$key]['context'] = $task->getContext();
            $task->getStartTime() ?
                $resultArray[$key]['startTime'] = $task->getStartTime()->format('Y-m-d H:i:s'):
                $resultArray[$key]['startTime'] = $task->getStartTime();
        }

        return $response->setContent(json_encode($resultArray));
    }
}

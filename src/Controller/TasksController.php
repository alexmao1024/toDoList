<?php

namespace App\Controller;

use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TasksController extends AbstractController
{
    #[Route('/tasks/show', name: 'tasks_show',methods: ['GET'])]
    public function TasksShow(TaskRepository $taskRepository): Response
    {
        $response = new Response();
        $tasks = $taskRepository->findAll();
        if (!$tasks)
        {
            return $this->json([]);
        }
        $resultArray = array();
        foreach ( $tasks as $key => $task )
        {
            $resultArray[$key]['taskId'] = $task->getId();
            $resultArray[$key]['userId'] = $task->getUserr()->getId();
            $resultArray[$key]['type'] = $task->getType();
            $resultArray[$key]['name'] = $task->getName();
            $resultArray[$key]['context'] = $task->getContext();
            $task->getStartTime() ?
                $resultArray[$key]['startTime'] = $task->getStartTime()->format('Y-m-d H:i:s'):
                $resultArray[$key]['startTime'] = $task->getStartTime();
        }

        return $response->setContent(json_encode($resultArray));
    }
}

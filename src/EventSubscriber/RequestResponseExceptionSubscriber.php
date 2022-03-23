<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelInterface;

class RequestResponseExceptionSubscriber implements EventSubscriberInterface
{
    private $env;

    public function __construct(KernelInterface $kernel)
    {
        $this->env = $kernel->getEnvironment();
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $method = $request->getMethod();
        $response = new Response();
        if ('OPTIONS'== $method)
        {
            $event->setResponse($response);
        }
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $response = new Response();
        $exception = $event->getThrowable();
        if ( $this->env !== 'dev' )
        {
            $response->setContent($exception->getMessage());
            if ($exception->getCode() == 1062)
            {
                $response->setStatusCode(500);
            }
            else
            {
                $response->setStatusCode($exception->getCode());
            }

            $event->setResponse($response);
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        if ($response) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET,POST,PUT,PATCH,DELETE,OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'content-type');
        }

    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.request' => ['onKernelRequest',999],
            'kernel.response' => ['onKernelResponse',999],
            'kernel.exception' => ['onKernelException',999]
        ];
    }
}

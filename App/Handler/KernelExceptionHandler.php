<?php

declare(strict_types=1);

namespace App\Handler;

use App\Exception\RedirectException;
use App\Factory\ViewRendererFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Handles kernel exceptions.
 */
class KernelExceptionHandler implements EventSubscriberInterface
{
    public function __construct(private ViewRendererFactory $viewRenderer)
    {
    }

    public function exceptionHandler(ExceptionEvent $event) : void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof NotFoundHttpException) {
            $this->renderErrorNotFound($event);
        } elseif ($exception instanceof HttpException && null === $event->getResponse()) {
            $this->renderException($event);
        } elseif ($exception instanceof RedirectException) {
            $response = new RedirectResponse($exception->getUrl(), $exception->getStatusCode());
            $event->setResponse($response);
        }
    }

    protected function renderErrorNotFound($event)
    {
        $message  = $event->getThrowable()->getMessage();
        $response = $this->viewRenderer->render(
            'exception/404.html.twig',
            [
                'error_message' => $message,
            ],
            403
        );

        $event->setResponse($response);
    }

    protected function renderException(ExceptionEvent $event) : void
    {
        /** @var HttpException $exception */
        $exception = $event->getThrowable();

        $event->setResponse(
            new Response(
                $exception->getMessage(),
                $exception->getStatusCode(),
                $exception->getHeaders()
            )
        );
    }

    // put your code here
    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::EXCEPTION => ['exceptionHandler', 128],
        ];
    }
}

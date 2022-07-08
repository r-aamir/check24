<?php

declare(strict_types=1);

namespace App\Handler;

use App\Exception\Security\AuthenticationException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionHandler implements EventSubscriberInterface
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var string */
    protected $env;

    public function __construct(
        $kernelEnvironment,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->env             = $kernelEnvironment;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function restrictionHandler(ExceptionEvent $event) : void
    {
        $exception = $event->getThrowable();
        if ($exception instanceof AuthenticationException) {
            $event->setResponse(
                new RedirectResponse($exception->getLoginPath())
            );
        }
    }

    /**
     * {@inheritdoc}
     * api.
     */
    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::EXCEPTION => [
                ['restrictionHandler', 100],
            ],
        ];
    }
}

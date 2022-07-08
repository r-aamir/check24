<?php

declare(strict_types=1);

namespace App\Handler;

use App\DependencyInjection\Authenticator\SessionAuthenticator;
use App\Exception\Security\AdminAuthenticationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

use function is_array;

class ControllerHandler implements EventSubscriberInterface
{
    public function __construct(
        private SessionAuthenticator $sessionAuthenticator
    ) {
    }

    public function securityFirewall(ControllerEvent $event) : void
    {
        $controller = $event->getController();
        if (
            is_array($controller)
//            && method_exists($controller[0], 'getControllerModule')
            && 'secure' === $controller[0]->getControllerModule()
            && $event->getRequest()->attributes->get('is-guest') !== 1
            && null === $this->sessionAuthenticator->getAuthenticatedUser()
        ) {
            throw new AdminAuthenticationException('You do not have accesss to this resource.');
        }
    }

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::CONTROLLER => [
                ['securityFirewall', 128],
            ],
        ];
    }
}

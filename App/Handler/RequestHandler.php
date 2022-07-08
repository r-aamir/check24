<?php

declare(strict_types=1);

namespace App\Handler;

use App\DependencyInjection\Authenticator\SessionAuthenticator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestHandler implements EventSubscriberInterface
{
    public function __construct(
        private SessionAuthenticator $sessionAuthenticator
    ) {
    }

    /**
     * Refreshes the user authentication using the hash stored in cookie
     */
    public function validateAuthentication(RequestEvent $event) : void
    {
        if (null !== $this->sessionAuthenticator->getAuthenticatedUser()) {
            return;
        }

        if (null !== $token = $this->sessionAuthenticator->getCookieToken()) {
            $tokenValues = $this->sessionAuthenticator->extractCookieToken($token);
            if (null !== $tokenValues) {
                $this->sessionAuthenticator->refreshAuthenticationFromToken(
                    $tokenValues['hash'],
                    $tokenValues['user_id']
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::REQUEST => [
                ['validateAuthentication', 128],
            ],
        ];
    }
}

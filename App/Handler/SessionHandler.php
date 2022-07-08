<?php

namespace App\Handler;

use App\Enum\SessionEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

use const PHP_INT_MAX;

class SessionHandler implements EventSubscriberInterface
{
    private bool $isTest;
    protected static $session;

    public function __construct(
        private string $sessionSavePath,
        private string $kernelCacheDir,
        string $kernelEnvironment
    ) {
        $this->isTest = 'test' === $kernelEnvironment;
    }

    public function sessionHandler(RequestEvent $event) : void
    {
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
            return;
        }

        if (null === $session = self::$session) {
            if ($this->isTest) {
                $sessionStorage = new MockFileSessionStorage(
                    $this->kernelCacheDir . '/sessions'
                );
            } else {
                $sessionStorage = $this->createSessionStorage();
            }

            self::$session = $session = new Session($sessionStorage);
        }

        $session->start();
        $event->getRequest()->setSession($session);
    }

    private function createSessionStorage() : SessionStorageInterface
    {
        $sessionStorage  = new NativeSessionStorage([
            'cookie_lifetime' => 0,
        ]);
        $sessionStorage->setSaveHandler(
            new NativeFileSessionHandler($this->sessionSavePath)
        );

        return $sessionStorage;
    }

    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::REQUEST => [
                ['sessionHandler', PHP_INT_MAX],
            ],
        ];
    }
}

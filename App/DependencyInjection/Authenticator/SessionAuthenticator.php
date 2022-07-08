<?php

namespace App\DependencyInjection\Authenticator;

use App\Enum\SessionEnum;
use App\Model\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

class SessionAuthenticator
{
    public function __construct(
        private RequestStack $requestStack
    ) {
    }

    private function getRequest() : Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    private function getSession() : Session
    {
        return $this->getRequest()->getSession();
    }

    /**
     * Get the authenticated user from session.
     */
    public function getAuthenticatedUser() : ?Users
    {
        return $this->getSession()->get('auth.user');
    }

    /**
     * Upon login, this sets the current authenticated user.
     *
     * @param Users $user       A UserInterface, or null if no further user should be stored
     */
    public function setAuthenticatedUser(Users $user) : void
    {
        $user->eraseCredentials();

        $this->getSession()->set('auth.user', $user);
    }

    /**
     * Upon logout, destructs user from session.
     */
    public function unsetAuthenticatedUser() : void
    {
        $this->getSession()->remove('auth.user');
    }

}

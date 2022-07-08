<?php

namespace App\Controller;

use App\DependencyInjection\Authenticator\SessionAuthenticator;
use App\DependencyInjection\Authenticator\UserAuthenticator;
use App\Factory\FormFactory;
use App\Factory\ViewRendererFactory;
use App\Form\LoginForm;
use App\Model\Users;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\PasswordHasher\Exception\InvalidPasswordException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class AuthenticationController extends BaseController
{
    public function __construct(ViewRendererFactory $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer;
    }

    public function loginAction(SessionAuthenticator $sessionAuthenticator, FormFactory $formFactory)
    {
        if ($sessionAuthenticator->getAuthenticatedUser() !== null) {
            return new RedirectResponse($this->getRoute('secure.home'));
        }

        /** @var string|null */
        $error = null;

        /** @var LoginForm */
        $loginForm = $formFactory->buildForm('form_login');

        if ($loginForm->isValid()) {
            if (null !== $user = $this->loginUser($loginForm)) {
                $sessionAuthenticator->setAuthenticatedUser($user);

                return new RedirectResponse($this->getRoute('secure.home'));
            }

            $error = 'Please check login and password.';
        }

        return $this->renderView('login', [
            'form'  => $loginForm->createView(),
            'error' => $error,
        ]);
    }

    /**
     * Authenticate using the submitted form.
     */
    private function loginUser(LoginForm $form) : ?Users
    {
        /** @var UserAuthenticator $userAuthenticator */
        $userAuthenticator = $this->container->get('authenticator');

        try {
            $user = $userAuthenticator->authenticateWithLoginForm($form);
        } catch (BadCredentialsException | InvalidPasswordException $error) {
            return null;
        }

        return $user;
    }

    public function getControllerModule() : string
    {
        return 'auth';
    }
}

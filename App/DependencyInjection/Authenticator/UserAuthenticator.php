<?php

namespace App\DependencyInjection\Authenticator;

use App\LoginTypeEnum;
use App\Form\LoginForm;
use App\Model\Users;
use App\Repository\UsersRepository;
use Symfony\Component\PasswordHasher\Exception\InvalidPasswordException;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;

use function sprintf;

/**
 * Authenticates a user against username/email and password.
 */
class UserAuthenticator
{
    public function __construct(
        private UsersRepository $usersRepository,
        private PasswordHasherFactoryInterface $passwordHasherFactory
    ) {
    }

    /**
     * User authentication.
     *
     * @throws BadCredentialsException
     * @throws InvalidPasswordException
     */
    public function authenticate(string $identity, string $password, ?LoginTypeEnum $identityType = null) : UserInterface
    {
        if (null === $user = $this->usersRepository->findOneByLogin($identity, $identityType)) {
            throw new BadCredentialsException(sprintf("User with identity '%s' does not exist.", $identity));
        }

        $passwordHasher = $this->passwordHasherFactory->getPasswordHasher($user);
        $hashedPassword = $user->getPassword();

        if (! $passwordHasher->verify($hashedPassword, $password)) {
            throw new InvalidPasswordException();
        }

        $user->eraseCredentials();

        /**
         * Prevents entity changes to be synced.
         */
        $this->usersRepository->detatch($user);

        return $user;
    }

    /**
     * User authentication from login form.
     */
    public function authenticateWithLoginForm(LoginForm $loginForm) : Users
    {
        $formData = $loginForm->getForm()->getData();

        return $this->authenticate($formData['username'], $formData['password']);
    }
}

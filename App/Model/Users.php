<?php

namespace App\Model;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Users.
 *
 * @ORM\Table(name="my_users", uniqueConstraints={@ORM\UniqueConstraint(name="idxEmail", columns={"email"}), @ORM\UniqueConstraint(name="idxLogin", columns={"login"})}, indexes={@ORM\Index(name="domainId", columns={"did"}), @ORM\Index(name="idxGoid", columns={"goid"}), @ORM\Index(name="idxPassword", columns={"password"}), @ORM\Index(name="idxFbid", columns={"fbid"}), @ORM\Index(name="idxVkid", columns={"vkid"})})
 * @ORM\Entity(repositoryClass="App\Repository\UsersRepository")
 */
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="fbid", type="string", length=30, nullable=true)
     *
     * @var string|null
     */
    private $fbid;

    /**
     * @ORM\Column(name="goid", type="string", length=30, nullable=true)
     *
     * @var string|null
     */
    private $goid;

    /**
     * @ORM\Column(name="user_type", type="integer", nullable=false)
     *
     * @var int
     */
    private $userType = 0;

    /**
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     *
     * @var string|null
     */
    private $email;

    /**
     * @ORM\Column(name="login", type="string", length=50, nullable=true)
     *
     * @var string|null
     */
    private $login;

    /**
     * @ORM\Column(name="password", type="string", length=255, nullable=false)
     *
     * @var string
     */
    private $password;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     *
     * @var string|null
     */
    private $name = '';

    /**
     * @ORM\Column(name="surname", type="string", length=255, nullable=true)
     *
     * @var string|null
     */
    private $surname;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     *
     * @var DateTime
     */
    private $createdAt = 'CURRENT_TIMESTAMP';

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getFbid() : ?string
    {
        return $this->fbid;
    }

    public function setFbid(?string $fbid) : self
    {
        $this->fbid = $fbid;

        return $this;
    }

    public function getGoid() : ?string
    {
        return $this->goid;
    }

    public function setGoid(?string $goid) : self
    {
        $this->goid = $goid;

        return $this;
    }

    public function getUserType() : ?int
    {
        return $this->userType;
    }

    public function setUserType(int $userType) : self
    {
        $this->userType = $userType;

        return $this;
    }

    public function getEmail() : ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email) : self
    {
        $this->email = $email;

        return $this;
    }

    public function getLogin() : ?string
    {
        return $this->login;
    }

    public function setLogin(?string $login) : self
    {
        $this->login = $login;

        return $this;
    }

    public function getPassword() : ?string
    {
        return $this->password;
    }

    public function setPassword(string $password) : self
    {
        $this->password = $password;

        return $this;
    }

    public function getName() : ?string
    {
        return $this->name;
    }

    public function setName(?string $name) : self
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname() : ?string
    {
        return $this->surname;
    }

    public function setSurname(?string $surname) : self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getCreatedAt() : ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt) : self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function eraseCredentials()
    {
        $this->password = '';
    }

    public function getRoles() : array
    {
        return [];
    }

    public function getUserIdentifier() : string
    {
        return $this->login;
    }
}

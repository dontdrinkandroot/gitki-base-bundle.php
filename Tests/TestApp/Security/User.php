<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\TestApp\Security;

use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, GitUserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $gitUserName;

    /**
     * @var string
     */
    private $gitUserEmail;

    /**
     * @var string[]
     */
    private $roles;

    public function __construct(string $username, string $gitUserName, string $gitUserEmail, array $roles = [])
    {
        $this->username = $username;
        $this->gitUserName = $gitUserName;
        $this->gitUserEmail = $gitUserEmail;
        $this->roles = $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getGitUserName()
    {
        return $this->gitUserName;
    }

    /**
     * {@inheritdoc}
     */
    public function getGitUserEmail()
    {
        return $this->gitUserEmail;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword(): string
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
        /* Noop */
    }
}
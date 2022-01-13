<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Utils;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class StaticUserProvider implements UserProviderInterface
{
    private $users;

    public function __construct()
    {
        $this->users = [];
        $this->users['user'] = new User('user', 'John Doe', 'johndoe@examle.com', ['ROLE_USER']);
        $this->users['admin'] = new User('admin', 'Mary Dane', 'marydane@examle.com', ['ROLE_USER', 'ROLE_ADMIN']);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username): UserInterface
    {
        if (!array_key_exists($username, $this->users)) {
            throw new UsernameNotFoundException();
        }

        return $this->users[$username];
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class): bool
    {
        return $class === User::class;
    }
}

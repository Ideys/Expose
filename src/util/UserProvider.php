<?php

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Doctrine\DBAL\Connection;

/**
 * User provider for the secured backend access.
 */
class UserProvider implements UserProviderInterface
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $db;

    public function __construct(Connection $connection)
    {
        $this->db = $connection;
    }

    public function loadUserByUsername($username)
    {
        $stmt = $this->db->executeQuery('SELECT * FROM expose_user WHERE username = ?', array(strtolower($username)));

        if (!$user = $stmt->fetch()) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        return new User($user['username'], $user['password'], explode(',', $user['roles']), true, true, true, true);
    }

    public function findAll()
    {
        $users = $this->db->fetchAll('SELECT * FROM expose_user');

        return $users;
    }

    public function addUser($security, $username, $password, array $roles = array('ROLE_USER'))
    {
        $user = new User($username, $password, $roles);
        $encoder = $security->getEncoder($user);
        $password = $encoder->encodePassword($password, $user->getSalt());

        $this->db->insert('expose_user', array(
            'username' => $username,
            'password' => $password,
            'roles' => implode(',', $roles),
        ));

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'Symfony\Component\Security\Core\User\User';
    }
}

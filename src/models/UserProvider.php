<?php

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\SecurityContext;
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

    const ROLE_SUPER_ADMIN  = 'ROLE_SUPER_ADMIN';
    const ROLE_ADMIN        = 'ROLE_ADMIN';
    const ROLE_USER         = 'ROLE_USER';

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

    public function find($id)
    {
        $user = $this->db->fetchAssoc('SELECT * FROM expose_user WHERE id = ?', array((int)$id));

        return $user;
    }

    public function findAll()
    {
        $users = $this->db->fetchAll('SELECT * FROM expose_user');

        return $users;
    }

    public function persistUser($security, $username, $password, array $roles = array('ROLE_USER'), $id = null)
    {
        $user = new User($username, $password, $roles);
        $data = array(
            'username' => $username,
            'roles' => implode(',', $roles),
        );

        if (!empty($password)) {
            $encoder = $security->getEncoder($user);
            $password = $encoder->encodePassword($password, $user->getSalt());
            $data += array('password' => $password);
        }

        if (empty($id)) {
            $this->db->insert('expose_user', $data);
        } else {
            $this->db->update('expose_user', $data, array('id' => $id));
        }

        return $user;
    }

    /**
     * Delete a user.
     * @param integer                                           $id
     * @param \Symfony\Component\Security\Core\SecurityContext  $security
     * @return boolean
     */
    public function deleteUser($id, SecurityContext $security)
    {
        $loggedUser = $security->getToken()->getUser();

        // A user could not delete himself
        $user = $this->db->fetchAssoc('SELECT * FROM expose_user WHERE id = ?', array($id));

        if ($loggedUser->getUsername() == $user['username']) {
            return false;
        }

        $this->db->delete('expose_user', array('id' => $id));

        return true;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public static function getRoles()
    {
        return array(
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
            self::ROLE_USER
        );
    }

    public static function getRolesChoice()
    {
        $keys = static::getRoles();
        $values = array_map(function($item){
            return 'user.role.'.$item;
        }, $keys);
        return array_combine($keys, $values);
    }

    public function supportsClass($class)
    {
        return $class === 'Symfony\Component\Security\Core\User\User';
    }
}

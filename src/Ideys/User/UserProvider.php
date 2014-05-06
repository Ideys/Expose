<?php

namespace Ideys\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\DBAL\Connection;

/**
 * User provider.
 */
class UserProvider implements UserProviderInterface
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $db;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    private $session;


    /**
     * Constructor.
     *
     * @param Connection    $connection
     * @param Session       $session
     */
    public function __construct(Connection $connection, Session $session)
    {
        $this->db = $connection;
        $this->session = $session;
    }

    /**
     * User loader for authentication.
     *
     * @param  string $username
     *
     * @return User|UserInterface
     *
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function loadUserByUsername($username)
    {
        $stmt = $this->db->executeQuery('SELECT * FROM expose_user WHERE username = ?', array(strtolower($username)));

        if (!$user = $stmt->fetch()) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        $profile = new Profile($user);

        // Check and update user last login
        if (null === $profile->getLastLogin()
                  || $profile->getLastLogin()->diff(new \DateTime('now'))->h > 0) {
            $profile->setLastLogin('now');
            $lastLogin = $profile->getLastLogin()->format('Y-m-d H:i:s');
            $this->db->update('expose_user', array('lastLogin' => $lastLogin), array('id' => $user['id']));
        }

        $this->session->set('profile', $profile);

        return new User($profile->getUsername(), $profile->getPassword(), $profile->getRoles(), true, true, true, true);
    }

    /**
     * Find a user profile.
     *
     * @param integer $id
     *
     * @return \Ideys\User\Profile
     */
    public function find($id)
    {
        $user = $this->db->fetchAssoc('SELECT * FROM expose_user WHERE id = ?', array((int)$id));

        $profile = new Profile($user);

        return $profile;
    }

    /**
     * Find all users profile.
     *
     * @return array
     */
    public function findAll()
    {
        $users = $this->db->fetchAll('SELECT * FROM expose_user');
        $profiles = array();

        foreach ($users as $user) {
            $profiles[] = new Profile($user);
        }

        return $profiles;
    }

    /**
     * Persist a user profile.
     *
     * @param \Symfony\Component\Security\Core\Encoder\EncoderFactory $security
     * @param \Ideys\User\Profile $profile
     *
     * @return \Symfony\Component\Security\Core\User\User
     */
    public function persist(EncoderFactory $security, Profile $profile)
    {
        $user = new User($profile->getUsername(), $profile->getPassword(), $profile->getRoles());

        if (null !== $profile->getPlainPassword()) {
            $encoder = $security->getEncoder($user);
            $profile->setPassword(
                    $encoder->encodePassword($profile->getPlainPassword(), $user->getSalt())
            );
        }

        $data = array(
            'username' => $profile->getUsername(),
            'password' => $profile->getPassword(),
            'email' => $profile->getEmail(),
            'gender' => $profile->getGender(),
            'firstname' => $profile->getFirstName(),
            'lastname' => $profile->getLastName(),
            'roles' => serialize($profile->getRoles()),
        );

        if (null === $profile->getId()) {
            $this->db->insert('expose_user', $data);
        } else {
            $this->db->update('expose_user', $data, array('id' => $profile->getId()));
        }

        return $user;
    }

    /**
     * Delete a user.
     *
     * @param integer                                           $id
     * @param \Symfony\Component\Security\Core\SecurityContext  $security
     *
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

    public function supportsClass($class)
    {
        return $class === 'Symfony\Component\Security\Core\User\User';
    }
}

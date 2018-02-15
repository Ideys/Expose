<?php

namespace Ideys\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\DBAL\Connection;

class UserProvider implements UserProviderInterface
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * @var Session
     */
    private $session;

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
     * @return UserInterface
     *
     * @throws \Exception
     */
    public function loadUserByUsername($username)
    {
        $stmt = $this->db->executeQuery('SELECT * FROM '.TABLE_PREFIX.'user WHERE username = ?', array(strtolower($username)));

        if (!$user = $stmt->fetch()) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        $profile = new Profile($user);

        // Check and update user last login
        if (null === $profile->getLastLogin()
                  || $profile->getLastLogin()->diff(new \DateTime('now'))->h > 0) {
            $profile->setLastLogin('now');
            $lastLogin = $profile->getLastLogin()->format('Y-m-d H:i:s');
            $this->db->update(TABLE_PREFIX.'user', array('lastLogin' => $lastLogin), array('id' => $user['id']));
        }

        $this->session->set('profile', $profile);

        return new User($profile->getUsername(), $profile->getPassword(), $profile->getRoles(), true, true, true, true);
    }

    public function find($id): Profile
    {
        $user = $this->db->fetchAssoc('SELECT * FROM '.TABLE_PREFIX.'user WHERE id = ?', array((int)$id));

        $profile = new Profile($user);

        return $profile;
    }

    public function findAll(): array
    {
        $users = $this->db->fetchAll('SELECT * FROM '.TABLE_PREFIX.'user');
        $profiles = array();

        foreach ($users as $user) {
            $profiles[] = new Profile($user);
        }

        return $profiles;
    }

    public function persist(EncoderFactory $security, Profile $profile): User
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
            'phone' => $profile->getPhone(),
            'mobile' => $profile->getMobile(),
            'website' => $profile->getWebsite(),
            'address' => $profile->getAddress(),
            'gender' => $profile->getGender(),
            'firstname' => $profile->getFirstName(),
            'lastname' => $profile->getLastName(),
            'organization' => $profile->getOrganization(),
            'roles' => serialize($profile->getRoles()),
            'groups' => serialize($profile->getGroupsId()),
        );

        if (null === $profile->getId()) {
            $this->db->insert(TABLE_PREFIX.'user', $data);
        } else {
            $this->db->update(TABLE_PREFIX.'user', $data, array('id' => $profile->getId()));
        }

        return $user;
    }

    public function deleteUser($id, TokenStorageInterface $security): bool
    {
        $loggedUser = $security->getToken()->getUser();

        // A user could not delete himself
        $user = $this->db->fetchAssoc('SELECT * FROM '.TABLE_PREFIX.'user WHERE id = ?', array($id));

        if ($loggedUser->getUsername() == $user['username']) {
            return false;
        }

        $this->db->delete(TABLE_PREFIX.'user', array('id' => $id));

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

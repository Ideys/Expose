<?php

namespace Ideys\User;

/**
 * Profile model.
 */
class Profile
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $plainPassword;

    /**
     * @var string
     */
    private $gender;

    /**
     * @var string
     */
    private $firstname;

    /**
     * @var string
     */
    private $lastname;

    /**
     * @var string
     */
    private $email;

    /**
     * @var array
     */
    private $roles = array(self::ROLE_USER);
    const ROLE_SUPER_ADMIN  = 'ROLE_SUPER_ADMIN';
    const ROLE_ADMIN        = 'ROLE_ADMIN';
    const ROLE_EDITOR       = 'ROLE_EDITOR';
    const ROLE_USER         = 'ROLE_USER';

    /**
     * @var \DateTime
     */
    private $lastLogin;

    /**
     * @param integer $id
     * @return Profile
     */
    private function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Constructor:
     * Hydrate user profile from an array.
     *
     * @param array $array
     */
    public function __construct($array = array())
    {
        foreach ($array as $prop => $value) {
            $this->{'set' . ucfirst($prop)}($value);
        }
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $username
     * @return Profile
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $password
     * @return Profile
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $plainPassword
     * @return Profile
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param string $gender
     * @return Profile
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Return gender choices.
     *
     * @return array
     */
    public static function getGenderChoice()
    {
        return array(
            'f' => 'user.f',
            'm' => 'user.m',
        );
    }

    /**
     * @param string $firstname
     * @return Profile
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $lastname
     * @return Profile
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $email
     * @return Profile
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $roles
     * @return Profile
     */
    public function setRoles($roles)
    {
        if (is_string($roles)) {
            $this->roles = unserialize($roles);
        } elseif (is_array($roles)) {
            $this->roles = $roles;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @return array
     */
    public static function getRolesKeys()
    {
        return array(
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
            self::ROLE_EDITOR,
            self::ROLE_USER
        );
    }

    /**
     * @return array
     */
    public static function getRolesChoice()
    {
        $keys = static::getRolesKeys();
        $values = array_map(function($item){
            return 'user.role.'.$item;
        }, $keys);
        return array_combine($keys, $values);
    }

    /**
     * @param mixed $lastLogin
     * @return Profile
     */
    public function setLastLogin($lastLogin)
    {
        if (is_string($lastLogin)) {
            $this->lastLogin = new \DateTime($lastLogin);
        } elseif ($lastLogin instanceof \DateTime) {
            $this->lastLogin = $lastLogin;
        }

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }
}

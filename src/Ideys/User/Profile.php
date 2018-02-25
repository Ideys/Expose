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
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $organization;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $website;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var string
     */
    private $mobile;

    /**
     * @var string
     */
    private $address;

    /**
     * @var array
     */
    private $roles = array(self::ROLE_USER);

    const ROLE_SUPER_ADMIN  = 'ROLE_SUPER_ADMIN';
    const ROLE_ADMIN        = 'ROLE_ADMIN';
    const ROLE_EDITOR       = 'ROLE_EDITOR';
    const ROLE_USER         = 'ROLE_USER';

    /**
     * @var array
     */
    private $groupsId = [];

    /**
     * @var \DateTime
     */
    private $lastLogin;

    /**
     * @param integer $id
     *
     * @return Profile
     */
    public function setId($id)
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
     *
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
     *
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
     *
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
     *
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

    public static function getGenderChoice(): array
    {
        return [
            'user.f' => 'f',
            'user.m' => 'm',
        ];
    }

    /**
     * @param string $firstName
     *
     * @return Profile
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $lastName
     *
     * @return Profile
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $organization
     *
     * @return Profile
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param string $email
     *
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
     * @param string $website
     *
     * @return Profile
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @param string $phone
     *
     * @return Profile
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $mobile
     *
     * @return Profile
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param string $address
     *
     * @return Profile
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $roles
     *
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
        return array_combine($values, $keys);
    }

    /**
     * @return array
     */
    public function getGroupsId()
    {
        return $this->groupsId;
    }

    /**
     * @param array|string $groupsId
     *
     * @return Profile
     */
    public function setGroupsId($groupsId)
    {
        if (is_string($groupsId)) {
            $this->groupsId = (empty($groupsId)) ? [] : unserialize($groupsId);
        } elseif (is_array($groupsId)) {
            $this->groupsId = $groupsId;
        }

        return $this;
    }

    /**
     * Alias of setGroupsId
     *
     * @param array|string $groupsId
     *
     * @return Profile
     */
    public function setGroups($groupsId)
    {
        return $this->setGroupsId($groupsId);
    }

    /**
     * @param mixed $lastLogin
     *
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

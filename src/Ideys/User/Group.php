<?php

namespace Ideys\User;

/**
 * Group entity.
 */
class Group
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $hierarchy = 0;

    /**
     * @param int $id
     *
     * @return Group
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     *
     * @return Group
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param int $hierarchy
     *
     * @return Group
     */
    public function setHierarchy($hierarchy)
    {
        $this->hierarchy = $hierarchy;

        return $this;
    }

    /**
     * @return int
     */
    public function getHierarchy()
    {
        return $this->hierarchy;
    }
}

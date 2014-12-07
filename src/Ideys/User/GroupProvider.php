<?php

namespace Ideys\User;

use Doctrine\DBAL\Connection;

/**
 * Group provider.
 */
class GroupProvider
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $db;

    /**
     * Constructor.
     *
     * @param Connection    $connection
     */
    public function __construct(Connection $connection)
    {
        $this->db = $connection;
    }

    /**
     * Find a user profile.
     *
     * @param integer $id
     *
     * @return \Ideys\User\Group
     */
    public function find($id)
    {
        $entity = $this->db->fetchAssoc('SELECT * FROM expose_user_group WHERE id = ?', array((int)$id));

        $group = new Group($entity);

        return $group;
    }

    /**
     * Find all users groups.
     *
     * @return Group[]
     */
    public function findAll()
    {
        $entities = $this->db->fetchAll('SELECT * FROM expose_user_group');
        $groups = array();

        foreach ($entities as $entity) {
            $groups[] = new Group($entity);
        }

        return $groups;
    }

    /**
     * Delete a group.
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteUser($id)
    {
        $deleted = $this->db->delete('expose_user_group', array('id' => $id));

        return $deleted > 0;
    }
}

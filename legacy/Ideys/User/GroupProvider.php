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
     * @param int $id
     *
     * @return Group
     */
    public function find($id)
    {
        $entity = $this->db->fetchAssoc('SELECT * FROM '.TABLE_PREFIX.'user_group WHERE id = ?', array((int)$id));

        return $this->hydrateGroup($entity);
    }

    /**
     * Find all users groups.
     *
     * @return Group[]
     */
    public function findAll()
    {
        $entities = $this->db->fetchAll('SELECT * FROM '.TABLE_PREFIX.'user_group ORDER BY hierarchy');
        $groups = array();

        foreach ($entities as $entity) {
            $groups[] = $this->hydrateGroup($entity);
        }

        return $groups;
    }

    /**
     * @param array $data
     *
     * @return Group
     */
    private function hydrateGroup($data)
    {
         return (new Group())
             ->setId($data['id'])
            ->setName($data['name'])
            ->setHierarchy($data['hierarchy']);
    }

    /**
     * Persist a group.
     *
     * @param Group $group
     */
    public function persist(Group $group)
    {
        $data = array(
            'name' => $group->getName(),
            'hierarchy' => $group->getHierarchy(),
        );

        if (null === $group->getId()) {
            $this->db->insert(TABLE_PREFIX.'user_group', $data);
        } else {
            $this->db->update(TABLE_PREFIX.'user_group', $data, array('id' => $group->getId()));
        }
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
        $deleted = $this->db->delete(TABLE_PREFIX.'user_group', array('id' => $id));

        return $deleted > 0;
    }
}

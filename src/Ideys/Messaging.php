<?php

namespace Ideys;

use Doctrine\DBAL\Connection;

/**
 * Contact manager.
 */
class Messaging
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $db;


    /**
     * Constructor: inject required Silex dependencies.
     *
     * @param array $app
     */
    public function __construct(Connection $connection)
    {
        $this->db = $connection;
    }

    /**
     * Add a message.
     */
    public function create($name, $email, $message)
    {
        $now = new \DateTime();
        $this->db->insert('expose_messaging', array(
            'name' => $name,
            'email' => $email,
            'message' => $message,
            'date' => $now->format('Y-m-d H:i:s'),
        ));
    }

    /**
     * Delete a message.
     * @param integer $id
     */
    public function delete($id)
    {
        $this->db->delete('expose_messaging', array('id' => $id));
    }

    /**
     * Retrieve all messages.
     */
    public function findAll()
    {
        $messages = $this->db->fetchAll('SELECT * FROM expose_messaging');

        return $messages;
    }
}

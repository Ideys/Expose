<?php

namespace Ideys\Messaging;

use Ideys\Settings\Settings;
use Doctrine\DBAL\Connection;
use Symfony\Component\Translation\Translator;

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
     * Constructor.
     *
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->db = $connection;
    }

    /**
     * Save a message on database.
     *
     * @param Message
     */
    public function persist(Message $message)
    {
        $this->db->insert('expose_messaging', array(
            'name' => $message->getName(),
            'email' => $message->getEmail(),
            'subject' => $message->getSubject(),
            'message' => $message->getMessage(),
            'date' => $message->getDate()->format('Y-m-d H:i:s'),
        ));
    }

    /**
     * Archive / Restore a message.
     *
     * @param integer $id
     */
    public function archive($id)
    {
        $this->db->executeQuery('UPDATE expose_messaging SET archive = NOT archive '
          . 'WHERE id = :id',
            array('id' => $id)
        );
    }

    /**
     * Delete a message.
     *
     * @param integer $id
     */
    public function delete($id)
    {
        $this->db->delete('expose_messaging', array('id' => $id));
    }

    /**
     * Retrieve active messages.
     *
     * @return array
     */
    public function findAll()
    {
        $messages = $this->db->fetchAll(
                'SELECT * FROM expose_messaging ' .
                'WHERE archive = 0');

        return $messages;
    }

    /**
     * Retrieve all messages.
     *
     * @return array
     */
    public function findArchived()
    {
        $messages = $this->db->fetchAll(
                'SELECT * FROM expose_messaging ' .
                'WHERE archive = 1');

        return $messages;
    }

    /**
     * Send a message by email.
     *
     * @param \Ideys\Settings\Settings                  $settings
     * @param \Symfony\Component\Translation\Translator $translator
     * @param \Ideys\Messaging\Message                  $message
     *
     * @return boolean
     */
    public function sendByEmail(Settings $settings, Translator $translator, Message $message)
    {
        $sendTo = $settings->contactSendToEmail;

        if (false === filter_var($sendTo, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $mailSubject = $translator->trans('contact.send.to.email.subject', array(
            '%sitename%' => $settings->name,
            '%sender_name%' => $message->getName(),
            '%sender_email%' => $message->getEmail(),
        ));

        // In case any of our lines are larger than 70 characters, we should use wordwrap()
        $mailMessage = wordwrap($message->getMessage(), 70, "\r\n");
        if ($message->hasSubject()) {
            $mailMessage = $message->getSubject() . "\r\n\r\n" . $mailMessage;
        }

        // Send
        return mail($sendTo, $mailSubject, $mailMessage);
    }
}

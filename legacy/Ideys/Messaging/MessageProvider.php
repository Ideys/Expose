<?php

namespace Ideys\Messaging;

use Ideys\Settings\Settings;
use Doctrine\DBAL\Connection;
use Symfony\Component\Translation\Translator;

/**
 * Message provider.
 */
class MessageProvider
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
        $this->db->insert(TABLE_PREFIX.'messaging', array(
            'name' => $message->getName(),
            'email' => $message->getEmail(),
            'subject' => $message->getSubject(),
            'message' => $message->getMessage(),
            'date' => $message->getDate()->format('Y-m-d H:i:s'),
        ));
    }

    /**
     * Mark a message as read.
     *
     * @param integer $id
     */
    public function markAsRead($id)
    {
        $this->db->executeQuery(
            'UPDATE '.TABLE_PREFIX.'messaging '
          . 'SET read_at = :datetime '
          . 'WHERE id = :id',
            array(
                'datetime' => (new \DateTime())->format('Y-m-d H:i:s'),
                'id' => $id,
            )
        );
    }

    /**
     * Archive / Restore a message.
     *
     * @param integer $id
     */
    public function archive($id)
    {
        $this->db->executeQuery(
            'UPDATE '.TABLE_PREFIX.'messaging '
          . 'SET archive = NOT archive '
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
        $this->db->delete(TABLE_PREFIX.'messaging', array('id' => $id));
    }

    /**
     * Retrieve active messages.
     *
     * @return array
     */
    public function findAll()
    {
        return $this->extractMessages();
    }

    /**
     * Retrieve archived messages.
     *
     * @return array
     */
    public function findArchived()
    {
        return $this->extractMessages(true);
    }

    /**
     * Messages extractor.
     *
     * @param boolean $archive
     *
     * @return array
     */
    private function extractMessages($archive = false)
    {
        $messages =  array();

        $results = $this->db->fetchAll(
            ' SELECT * FROM '.TABLE_PREFIX.'messaging' .
            ' WHERE archive = ' . (int) $archive .
            ' ORDER BY date DESC'
        );

        foreach ($results as $result) {
            $message = new Message();

            $message
                ->setId($result['id'])
                ->setDate(new \DateTime($result['date']))
                ->setName($result['name'])
                ->setEmail($result['email'])
                ->setSubject($result['subject'])
                ->setMessage($result['message'])
                ->setReadAt(empty($result['read_at']) ? null : new \DateTime($result['read_at']));

            $messages[] = $message;
        }

        return $messages;
    }

    /**
     * Count read and unread messages.
     *
     * @return integer
     */
    public function countUnread()
    {
        return $this->countMessages('unread');
    }

    /**
     * Count read and unread messages.
     *
     * @return integer
     */
    public function countRead()
    {
        return $this->countMessages('read');
    }

    /**
     * Count total of archived messages.
     *
     * @return integer
     */
    public function countArchived()
    {
        return $this->countMessages('archived');
    }

    /**
     * Return all messages count.
     *
     * @return array
     */
    public function countAll()
    {
        return array(
            'unread' => $this->countUnread(),
            'read' => $this->countRead(),
            'archived' => $this->countArchived(),
        );
    }

    /**
     * Messages counter.
     *
     * @param string $filter
     *
     * @return integer
     */
    private function countMessages($filter)
    {
        $sqlStatement = 'SELECT count(id) AS total FROM '.TABLE_PREFIX.'messaging ';

        switch ($filter) {
            case 'unread':
                $sqlStatement .= 'WHERE archive = 0 AND read_at IS NULL';
                break;
            case 'read':
                $sqlStatement .= 'WHERE archive = 0 AND read_at IS NOT NULL';
                break;
            case 'archived':
                $sqlStatement .= 'WHERE archive = 1';
                break;
        }

        $counter = $this->db->fetchAssoc($sqlStatement);

        return $counter['total'];
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
        $sendTo = $settings->getContactSendToEmail();

        if (false === filter_var($sendTo, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $mailSubject = $translator->trans('contact.send.to.email.subject', array(
            '%sitename%' => $settings->getName(),
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

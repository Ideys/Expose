<?php

namespace Ideys\Messaging;

/**
 * Contact message entity.
 */
class Message
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $message;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var \DateTime
     */
    private $readAt;

    /**
     * @var string
     */
    private $spicedHamQuestion;

    /**
     * @var string
     */
    private $spicedHamAnswer;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param integer
     *
     * @return Message
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Message
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Message
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Test if message has a subject.
     *
     * @return boolean
     */
    public function hasSubject()
    {
        return (null !== $this->subject);
    }

    /**
     * Set subject
     *
     * @param string $subject
     *
     * @return Message
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return Message
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        if (null === $this->date) {
            return new \DateTime();
        }

        return $this->date;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Message
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get readAt
     *
     * @return \DateTime
     */
    public function getReadAt()
    {
        return $this->readAt;
    }

    /**
     * Test if message is read.
     *
     * @return boolean
     */
    public function isRead()
    {
        return $this->readAt instanceof \DateTime;
    }

    /**
     * Set readAt
     *
     * @param \DateTime $readAt
     *
     * @return Message
     */
    public function setReadAt(\DateTime $readAt = null)
    {
        $this->readAt = $readAt;

        return $this;
    }

    /**
     * @param string $spicedHamQuestion
     *
     * @return Message
     */
    public function setSpicedHamQuestion($spicedHamQuestion)
    {
        $this->spicedHamQuestion = $spicedHamQuestion;

        return $this;
    }

    /**
     * @return string
     */
    public function getSpicedHamQuestion()
    {
        return $this->spicedHamQuestion;
    }

    /**
     * @param string $spicedHamAnswer
     *
     * @return Message
     */
    public function setSpicedHamAnswer($spicedHamAnswer)
    {
        $this->spicedHamAnswer = $spicedHamAnswer;

        return $this;
    }

    /**
     * @return string
     */
    public function getSpicedHamAnswer()
    {
        return $this->spicedHamAnswer;
    }
}

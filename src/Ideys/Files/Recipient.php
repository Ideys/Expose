<?php

namespace Ideys\Files;

use Ideys\String;

/**
 * File recipient object.
 */
class Recipient
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Ideys\Files\File
     */
    private $file;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $token;

    /**
     * @var integer
     */
    private $downloadCounter = 0;

    /**
     * @var array
     */
    private $downloadLogs = array();


    /**
     * @param integer $id
     *
     * @return \Ideys\Files\Recipient
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Ideys\Files\File $file
     *
     * @return \Ideys\Files\Recipient
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return \Ideys\Files\File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $name
     *
     * @return \Ideys\Files\Recipient
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
     * @param string $token
     *
     * @return \Ideys\Files\Recipient
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        if (null === $this->token) {
            $this->token = String::generateToken();
        }
        return $this->token;
    }

    /**
     * @param integer $downloadCounter
     *
     * @return \Ideys\Files\Recipient
     */
    public function setDownloadCounter($downloadCounter)
    {
        $this->downloadCounter = $downloadCounter;

        return $this;
    }

    /**
     * Increments download counter.
     *
     * @return \Ideys\Files\Recipient
     */
    public function incrDownloadCounter()
    {
        $this->downloadCounter++;

        return $this;
    }

    /**
     * @return integer
     */
    public function getDownloadCounter()
    {
        return $this->downloadCounter;
    }

    /**
     * @param array $downloadLogs
     *
     * @return \Ideys\Files\Recipient
     */
    public function setDownloadLogs($downloadLogs)
    {
        $this->downloadLogs = $downloadLogs;

        return $this;
    }

    /**
     * Add a download log timestamp.
     *
     * @return \Ideys\Files\Recipient
     */
    public function addDownloadLogs()
    {
        $this->downloadLogs[] = (new \DateTime())->format('Y-m-d H:i:s');

        return $this;
    }

    /**
     * @return array
     */
    public function getDownloadLogs()
    {
        return $this->downloadLogs;
    }
}

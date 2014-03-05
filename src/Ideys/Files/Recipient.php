<?php

namespace Ideys\Files;

/**
 * File recipient object.
 */
class Recipient
{
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
     * @return integer
     */
    public function getDownloadCounter()
    {
        return $this->downloadCounter;
    }

    /**
     * @return array
     */
    public function getDownloadLogs()
    {
        return $this->downloadLogs;
    }
}

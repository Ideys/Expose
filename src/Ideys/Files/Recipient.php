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
     * @var array
     */
    private $download_logs;

    /**
     * @return array
     */
    public function getDownloadLogs()
    {
        return $this->download_logs;
    }
}

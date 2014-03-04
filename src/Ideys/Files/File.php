<?php

namespace Ideys\Files;

/**
 * File object.
 */
class File
{
    private $title;
    private $name;
    private $slug;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return WEB_DIR.'/../downloads/'.$this->name;
    }
}

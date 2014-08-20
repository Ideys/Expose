<?php

namespace Ideys\Content\Section\Entity;

/**
 * Directory manager.
 */
class Dir extends Section
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->type = Section::SECTION_DIR;
    }
}

<?php

namespace App\Content\Section;

/**
 * Directory manager.
 */
class Dir extends Section
{
    public function __construct()
    {
        $this->type = Section::SECTION_DIR;
    }
}

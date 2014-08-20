<?php

namespace Ideys\Content\Item\Entity;

/**
 * Html Page class.
 */
class Page extends Item
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->type = self::ITEM_PAGE;
    }
}

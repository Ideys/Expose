<?php

namespace App\Content\Item;

/**
 * Html Page class.
 */
class Page extends Item
{
    public function __construct()
    {
        $this->type = self::ITEM_PAGE;
    }
}

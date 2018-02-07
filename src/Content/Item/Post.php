<?php

namespace App\Content\Item;

/**
 * Blog Post class.
 */
class Post extends Item
{
    public function __construct()
    {
        $this->type = self::ITEM_POST;
    }
}

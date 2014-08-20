<?php

namespace Ideys\Content\Item\Entity;

/**
 * Blog Post class.
 */
class Post extends Item
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->type = self::ITEM_POST;
    }
}

<?php

namespace Ideys\Content\Item;

use Ideys\Content\ContentInterface;

/**
 * Blog Post class.
 */
class Post extends Item implements ContentInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getParameters()
    {
        return array();
    }
}

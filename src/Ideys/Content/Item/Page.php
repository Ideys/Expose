<?php

namespace Ideys\Content\Item;

use Ideys\Content\ContentInterface;

/**
 * Html Page class.
 */
class Page extends Item implements ContentInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getParameters()
    {
        return array();
    }
}

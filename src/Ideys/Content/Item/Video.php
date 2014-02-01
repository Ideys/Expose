<?php

namespace Ideys\Content\Item;

use Ideys\Content\ContentInterface;

/**
 * Channel Video class.
 */
class Video extends Item implements ContentInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getParameters()
    {
        return array();
    }
}

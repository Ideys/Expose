<?php

namespace Ideys\Content\Item;

use Ideys\Content\ContentInterface;

/**
 * Map item class.
 */
class Map extends Item implements ContentInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getParameters()
    {
        return array();
    }
}

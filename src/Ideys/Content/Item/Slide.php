<?php

namespace Ideys\Content\Item;

use Ideys\Content\ContentInterface;

/**
 * Gallery Slide class.
 */
class Slide extends Item implements ContentInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getParameters()
    {
        return array();
    }
}

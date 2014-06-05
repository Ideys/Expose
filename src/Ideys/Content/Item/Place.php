<?php

namespace Ideys\Content\Item;

use Ideys\Content\ContentInterface;

/**
 * Maps places item class.
 */
class Place extends Item implements ContentInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getParameters()
    {
        return array(
            'latitude' => '0',
            'longitude' => '0',
        );
    }
}

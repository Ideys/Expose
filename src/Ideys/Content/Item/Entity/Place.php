<?php

namespace Ideys\Content\Item\Entity;

/**
 * Maps places item class.
 */
class Place extends Item
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->type = self::ITEM_PLACE;
    }
}

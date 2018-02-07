<?php

namespace App\Content\Item;

/**
 * Maps places item class.
 */
class Place extends Item
{
    public function __construct()
    {
        $this->type = self::ITEM_PLACE;
    }
}

<?php

namespace Ideys\Content\Item;

/**
 * Items prototype class.
 */
abstract class Item
{
    use \Ideys\Content\ContentTrait;

    /**
     * Constructor.
     *
     * @param array $entity
     */
    public function __construct(array $entity)
    {
        $this->attributes = $entity;
        $this->parameters = unserialize($entity['parameters']);
    }
}

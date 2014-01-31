<?php

/**
 * Contents parent class.
 */
class ContentItem implements ContentInterface
{
    use \ContentParametersTrait;

    /**
     * {@inheritdoc}
     */
    public static function getParameters()
    {
        return array();
    }

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

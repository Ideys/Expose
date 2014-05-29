<?php

namespace Ideys\Content\Item;

use Ideys\Content\ContentInterface;

/**
 * Gallery Slide class.
 */
class Slide extends Item implements ContentInterface
{
    /**
     * @var array
     */
    protected $metaData = array();

    /**
     * {@inheritdoc}
     */
    public static function getParameters()
    {
        return array();
    }

    /**
     * Set meta data.
     *
     * @param array $metaData
     */
    public function setMetaData($metaData)
    {
        $this->metaData = $metaData;
    }

    /**
     * Get meta data.
     *
     * @return array
     */
    public function getMetaData()
    {
        return $this->metaData;
    }
}

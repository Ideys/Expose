<?php

namespace Ideys\Content\Section;

use Ideys\Content\ContentInterface;
use Ideys\Content\SectionInterface;

/**
 * Maps content manager.
 */
class Maps extends Section implements ContentInterface, SectionInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getParameters()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultItemType()
    {
        return 'Place';
    }

    /**
     * {@inheritdoc}
     */
    public function isSlidesHolder()
    {
        return true;
    }
}

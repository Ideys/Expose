<?php

namespace Ideys\Content\Section;

use Ideys\Content\ContentInterface;

/**
 * Maps content manager.
 */
class Maps extends Section implements ContentInterface
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
    public function isSlidesHolder()
    {
        return true;
    }
}

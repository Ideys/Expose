<?php

namespace Ideys\Content\Section;

use Ideys\Content\ContentInterface;

/**
 * Channel content manager.
 */
class Channel extends Section implements ContentInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getParameters()
    {
        return array();
    }
}

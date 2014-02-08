<?php

namespace Ideys\Content\Section;

use Ideys\Content\ContentInterface;

/**
 * Directory manager.
 */
class Dir extends Section implements ContentInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getParameters()
    {
        return array();
    }
}

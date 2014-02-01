<?php

namespace Ideys\Content\Section;

use Ideys\Content\ContentInterface;

/**
 * HTML content manager.
 */
class Html extends Section implements ContentInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getParameters()
    {
        return array();
    }

    /**
     * Return page content first page.
     */
    public function getFirstPage()
    {
        $items = $this->getItems();

        return array_pop($items);
    }
}

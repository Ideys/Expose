<?php

namespace Ideys\Content\Section;

use Ideys\Content\Item;

/**
 * HTML content manager.
 */
class Html extends Section implements SectionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefaultItems()
    {
        return $this->getPages();
    }

    /**
     * Return Page Items.
     *
     * @return array
     */
    public function getPages()
    {
        return $this->getItemsOfType(Item\Item::ITEM_PAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function isSlidesHolder()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isComposite()
    {
        return true;
    }

    /**
     * Return page content first page.
     */
    public function getFirstPage()
    {
        $items = $this->getItems('Page');

        return array_pop($items);
    }
}

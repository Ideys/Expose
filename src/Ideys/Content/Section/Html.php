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
     * Return HTML section first page.
     */
    public function getFirstPage()
    {
        $pages = $this->getPages();

        return array_pop($pages);
    }

    /**
     * Test if HTML Section has some Page Items.
     *
     * @return boolean
     */
    public function hasPages()
    {
        return $this->hasItemsOfType(Item\Item::ITEM_PAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function countMainItems()
    {
        return count($this->getPages());
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
}

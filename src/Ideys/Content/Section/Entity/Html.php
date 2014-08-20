<?php

namespace Ideys\Content\Section\Entity;

use Ideys\Content\Item\Entity;

/**
 * HTML content manager.
 */
class Html extends Section implements SectionInterface
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->type = Section::SECTION_HTML;
    }

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
        return $this->getItemsOfType(Entity\Item::ITEM_PAGE);
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
        return $this->hasItemsOfType(Entity\Item::ITEM_PAGE);
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

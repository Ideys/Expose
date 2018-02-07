<?php

namespace App\Content\Section;
use App\Content\Item\Item;
use App\Content\Item\Page;

/**
 * HTML content manager.
 */
class Html extends Section implements SectionInterface
{
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
     * @return Page[]
     */
    public function getPages()
    {
        return $this->getItemsOfType(Item::ITEM_PAGE);
    }

    /**
     * Return HTML section first page.
     *
     * @return Page|null
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
        return $this->hasItemsOfType(Item::ITEM_PAGE);
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

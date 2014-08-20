<?php

namespace Ideys\Content\Section\Entity;

use Ideys\Content\Item\Entity;

/**
 * Blog section manager.
 */
class Blog extends Section implements SectionInterface
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->type = Section::SECTION_BLOG;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultItems()
    {
        return $this->getPosts();
    }

    /**
     * {@inheritdoc}
     */
    public function countMainItems()
    {
        return count($this->getPosts());
    }

    /**
     * Get Post Items.
     *
     * @return array
     */
    public function getPosts()
    {
        return $this->getItemsOfType(Entity\Item::ITEM_POST);
    }

    /**
     * Test if Blog has some Post Items.
     *
     * @return boolean
     */
    public function hasPosts()
    {
        return $this->hasItemsOfType(Entity\Item::ITEM_POST);
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

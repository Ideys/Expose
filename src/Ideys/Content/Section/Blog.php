<?php

namespace Ideys\Content\Section;

use Ideys\Content\ContentFactory;
use Ideys\Content\Item;

/**
 * Blog section manager.
 */
class Blog extends Section implements SectionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefaultItems()
    {
        return $this->getPosts();
    }

    /**
     * Get Post Items.
     *
     * @return array
     */
    public function getPosts()
    {
        return $this->getItemsOfType(Item\Item::ITEM_POST);
    }

    /**
     * Test if Blog has some Post Items.
     *
     * @return boolean
     */
    public function hasPosts()
    {
        return $this->hasItemsOfType(Item\Item::ITEM_POST);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSqlSelectItem()
    {
        return ContentFactory::getSqlSelectItem() .
            'WHERE i.expose_section_id = ? '.
            'ORDER BY i.posting_date DESC, i.hierarchy ASC ';
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

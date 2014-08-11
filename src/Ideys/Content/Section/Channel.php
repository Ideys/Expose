<?php

namespace Ideys\Content\Section;

use Ideys\Content\Item;

/**
 * Channel content manager.
 */
class Channel extends Section implements SectionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefaultItems()
    {
        return $this->getVideos();
    }

    /**
     * Get Video Items.
     *
     * @return array
     */
    public function getVideos()
    {
        return $this->getItemsOfType(Item\Item::ITEM_VIDEO);
    }

    /**
     * Test if Channel has some Video Items.
     *
     * @return boolean
     */
    public function hasVideos()
    {
        return $this->hasItemsOfType(Item\Item::ITEM_VIDEO);
    }

    /**
     * {@inheritdoc}
     */
    public function countMainItems()
    {
        return count($this->getVideos());
    }

    /**
     * Define if channel has more than one video.
     *
     * @return boolean
     */
    public function hasMultiplePages()
    {
        return (count($this->items) > 1);
    }

    /**
     * Return available video providers.
     *
     * @return array
     */
    public static function getProviderChoice()
    {
        return array(
            Item\Video::PROVIDER_VIMEO => ucfirst(Item\Video::PROVIDER_VIMEO),
            Item\Video::PROVIDER_DAILYMOTION => ucfirst(Item\Video::PROVIDER_DAILYMOTION),
            Item\Video::PROVIDER_YOUTUBE => ucfirst(Item\Video::PROVIDER_YOUTUBE),
        );
    }
}

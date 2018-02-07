<?php

namespace App\Content\Section;

use App\Content\Item\Item;
use App\Content\Item\Video;

/**
 * Channel content manager.
 */
class Channel extends Section implements SectionInterface
{
    public function __construct()
    {
        $this->type = Section::SECTION_CHANNEL;
    }

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
     * @return Video[]
     */
    public function getVideos()
    {
        return $this->getItemsOfType(Item::ITEM_VIDEO);
    }

    /**
     * Test if Channel has some Video Items.
     *
     * @return boolean
     */
    public function hasVideos()
    {
        return $this->hasItemsOfType(Item::ITEM_VIDEO);
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
            Video::PROVIDER_VIMEO => ucfirst(Video::PROVIDER_VIMEO),
            Video::PROVIDER_DAILYMOTION => ucfirst(Video::PROVIDER_DAILYMOTION),
            Video::PROVIDER_YOUTUBE => ucfirst(Video::PROVIDER_YOUTUBE),
        );
    }
}

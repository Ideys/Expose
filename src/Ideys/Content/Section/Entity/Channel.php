<?php

namespace Ideys\Content\Section\Entity;

use Ideys\Content\Item\Entity;

/**
 * Channel content manager.
 */
class Channel extends Section implements SectionInterface
{
    /**
     * Constructor.
     */
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
     * @return array
     */
    public function getVideos()
    {
        return $this->getItemsOfType(Entity\Item::ITEM_VIDEO);
    }

    /**
     * Test if Channel has some Video Items.
     *
     * @return boolean
     */
    public function hasVideos()
    {
        return $this->hasItemsOfType(Entity\Item::ITEM_VIDEO);
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
            Entity\Video::PROVIDER_VIMEO => ucfirst(Entity\Video::PROVIDER_VIMEO),
            Entity\Video::PROVIDER_DAILYMOTION => ucfirst(Entity\Video::PROVIDER_DAILYMOTION),
            Entity\Video::PROVIDER_YOUTUBE => ucfirst(Entity\Video::PROVIDER_YOUTUBE),
        );
    }
}

<?php

namespace Ideys\Content\Item\Entity;

/**
 * Channel Video class.
 */
class Video extends Item
{
    const PROVIDER_VIMEO = 'vimeo';
    const PROVIDER_DAILYMOTION = 'dailymotion';
    const PROVIDER_YOUTUBE = 'youtube';

    /**
     * Set provider (alias of setCategory)
     *
     * @param string $provider
     *
     * @return Video
     */
    public function setProvider($provider)
    {
        $this->category = $provider;

        return $this;
    }

    /**
     * Get provider (alias of getCategory)
     *
     * @return string
     */
    public function getProvider()
    {
        return $this->category;
    }
}

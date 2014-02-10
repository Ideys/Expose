<?php

namespace Ideys\Content\Item;

use Ideys\Content\ContentInterface;

/**
 * Channel Video class.
 */
class Video extends Item implements ContentInterface
{
    const PROVIDER_VIMEO = 'vimeo';
    const PROVIDER_DAILYMOTION = 'dailymotion';
    const PROVIDER_YOUTUBE = 'youtube';

    /**
     * {@inheritdoc}
     */
    public static function getParameters()
    {
        return array(
            'provider' => 'vimeo',
        );
    }
}

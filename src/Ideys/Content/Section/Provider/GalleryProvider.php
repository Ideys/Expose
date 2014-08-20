<?php

namespace Ideys\Content\Section\Provider;

use Ideys\Content\Item;

/**
 * Gallery section provider.
 */
class GalleryProvider extends SectionProvider
{
    /**
     * Return the Gallery directory path for Slides pictures storage.
     *
     * @return string
     */
    public static function getGalleryDir()
    {
        return WEB_DIR.'/gallery';
    }
}

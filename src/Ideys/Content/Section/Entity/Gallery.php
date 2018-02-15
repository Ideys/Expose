<?php

namespace Ideys\Content\Section\Entity;

use Ideys\Content\Item\Entity;
use Ideys\Content\Item\Entity\Slide;

/**
 * Gallery content manager.
 */
class Gallery extends Section implements SectionInterface
{
    const GALLERY_MODE_SLIDESHOW    = 'slideshow';
    const GALLERY_MODE_VERTICAL     = 'vertical';
    const GALLERY_MODE_MASONRY      = 'masonry';

    const SLIDE_MODE_SLIDE  = 'slide';
    const SLIDE_MODE_FADE   = 'fade';

    const SIZE_CENTERED     = 'centered';
    const SIZE_FULL_SCREEN  = 'full.screen';
    const SIZE_EXTENDED     = 'extended';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->type = Section::SECTION_GALLERY;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultItems()
    {
        return $this->getSlides();
    }

    /**
     * Return Slide Items.
     *
     * @return Slide[]
     */
    public function getSlides()
    {
        return $this->getItemsOfType(Entity\Item::ITEM_SLIDE);
    }

    /**
     * Test if Gallery has some Slide Items.
     *
     * @return boolean
     */
    public function hasSlides()
    {
        return $this->hasItemsOfType(Entity\Item::ITEM_SLIDE);
    }

    /**
     * {@inheritdoc}
     */
    public function countMainItems()
    {
        return count($this->getSlides());
    }

    /**
     * {@inheritdoc}
     */
    public function isSlidesHolder()
    {
        return true;
    }

    /**
     * Return gallery slides total weight.
     *
     * @return float
     */
    public function getWeight()
    {
        $weight = 0;

        foreach ($this->items as $slide) {
            if ($slide instanceof Entity\Slide) {
                $weight += $slide->getFileSize();
            }
        }

        return $weight;
    }

    /**
     * Test if gallery slides could have a link on current mode.
     *
     * @return boolean
     */
    public function isLinkable()
    {
        return in_array($this->getGalleryMode(), array(
            self::GALLERY_MODE_VERTICAL,
        ));
    }

    /**
     * Return gallery modes choices.
     *
     * @return array
     */
    public static function getGalleryModeChoice()
    {
        return array(
            'gallery.mode.slideshow' => self::GALLERY_MODE_SLIDESHOW,
            'gallery.mode.vertical'  => self::GALLERY_MODE_VERTICAL,
            'gallery.mode.masonry'   => self::GALLERY_MODE_MASONRY,
        );
    }

    /**
     * Return slide modes choices.
     *
     * @return array
     */
    public static function getSlideModeChoice()
    {
        return array(
           'gallery.slide.slide' => self::SLIDE_MODE_SLIDE,
           'gallery.slide.fade' => self::SLIDE_MODE_FADE,
        );
    }

    /**
     * Return gallery grid rows choices.
     *
     * @param integer $max
     *
     * @return array
     */
    public static function getGalleryGridRowsChoice($max = 12)
    {
        $rows = array();
        foreach (range(1, $max) as $row) {
            $rows['gallery.grid.rows'.$row] = (string)$row;
        }
        return $rows;
    }

    /**
     * @return string
     */
    public function getGalleryMode()
    {
        return $this->retrieveParameter('gallery_mode', self::GALLERY_MODE_SLIDESHOW);
    }

    /**
     * @param string $galleryMode
     *
     * @return Gallery
     */
    public function setGalleryMode($galleryMode)
    {
        $this->addParameter('gallery_mode', $galleryMode);

        return $this;
    }

    /**
     * @return string
     */
    public function getSlideMode()
    {
        return $this->retrieveParameter('slide_mode', self::SLIDE_MODE_SLIDE);
    }

    /**
     * @param string $slideMode
     *
     * @return Gallery
     */
    public function setSlideMode($slideMode)
    {
        $this->addParameter('slide_mode', $slideMode);

        return $this;
    }

    /**
     * @return string
     */
    public function getSize()
    {
        return $this->retrieveParameter('size', self::SIZE_CENTERED);
    }

    /**
     * @param string $size
     *
     * @return Gallery
     */
    public function setSize($size)
    {
        $this->addParameter('size', $size);

        return $this;
    }

    /**
     * Return slides size choices.
     *
     * @return array
     */
    public static function getSizeChoice()
    {
        return array(
            'gallery.slide.size.centered'    => self::SIZE_CENTERED,
            'gallery.slide.size.full.screen' => self::SIZE_FULL_SCREEN,
            'gallery.slide.size.extended'    => self::SIZE_EXTENDED,
        );
    }

    /**
     * @return string
     */
    public function getNavBar()
    {
        return $this->retrieveParameter('nav_bar', '0');
    }

    /**
     * @param string $navBar
     *
     * @return Gallery
     */
    public function setNavBar($navBar)
    {
        $this->addParameter('nav_bar', $navBar);

        return $this;
    }

    /**
     * @return string
     */
    public function getThumbList()
    {
        return $this->retrieveParameter('thumb_list', '0');
    }

    /**
     * @param string $thumbList
     *
     * @return Gallery
     */
    public function setThumbList($thumbList)
    {
        $this->addParameter('thumb_list', $thumbList);

        return $this;
    }

    /**
     * @return string
     */
    public function getGridRows()
    {
        return $this->retrieveParameter('grid_rows', '1');
    }

    /**
     * @param string $gridRows
     *
     * @return Gallery
     */
    public function setGridRows($gridRows)
    {
        $this->addParameter('grid_rows', $gridRows);

        return $this;
    }

    /**
     * @return string
     */
    public function getGridRowsMedium()
    {
        return $this->retrieveParameter('grid_rows_medium', '1');
    }

    /**
     * @param string $gridRowsMedium
     *
     * @return Gallery
     */
    public function setGridRowsMedium($gridRowsMedium)
    {
        $this->addParameter('grid_rows_medium', $gridRowsMedium);

        return $this;
    }

    /**
     * @return string
     */
    public function getGridRowsSmall()
    {
        return $this->retrieveParameter('grid_rows_small', '1');
    }

    /**
     * @param string $gridRowsSmall
     *
     * @return Gallery
     */
    public function setGridRowsSmall($gridRowsSmall)
    {
        $this->addParameter('grid_rows_small', $gridRowsSmall);

        return $this;
    }
}

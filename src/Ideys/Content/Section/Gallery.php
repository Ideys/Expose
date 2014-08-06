<?php

namespace Ideys\Content\Section;

use Ideys\Settings\Settings;
use Symfony\Component\Form\FormFactory;

/**
 * Gallery content manager.
 */
class Gallery extends Section implements SectionInterface
{
    /**
     * @var string
     */
    private $galleryMode = 'slideshow';

    /**
     * @var string
     */
    private $slideMode = 'slide';

    /**
     * @var string
     */
    private $extended = '0';

    /**
     * @var string
     */
    private $navBar = '0';

    /**
     * @var string
     */
    private $thumbList = '0';

    /**
     * @var string
     */
    private $gridRows = '1';

    /**
     * @var string
     */
    private $gridRowsMedium = '1';

    /**
     * @var string
     */
    private $gridRowsSmall = '1';

    /**
     * {@inheritdoc}
     */
    public static function getDefaultItemType()
    {
        return 'Slide';
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
            $weight += $slide->getFileSize();
        }

        return $weight;
    }

    /**
     * {@inheritdoc}
     */
    public function settingsForm(FormFactory $formFactory)
    {
        $formBuilder = $this->settingsFormBuilder($formFactory)
            ->add('gallery_mode', 'choice', array(
                'label' => 'gallery.mode.mode',
                'choices' => static::getGalleryModeChoice(),
            ))
            ->add('slide_mode', 'choice', array(
                'label' => 'gallery.slide.mode',
                'choices' => static::getSlideModeChoice(),
            ))
            ->add('extended', 'choice', array(
                'label' => 'gallery.mode.full.screen.extended',
                'choices' => Settings::getIOChoices(),
            ))
            ->add('nav_bar', 'choice', array(
                'label' => 'gallery.nav.bar.display',
                'choices' => Settings::getIOChoices(),
            ))
            ->add('thumb_list', 'choice', array(
                'label' => 'gallery.thumb.list.display',
                'choices' => Settings::getIOChoices(),
            ))
            ->add('grid_rows', 'choice', array(
                'label' => 'gallery.grid.rows',
                'choices' => static::getGalleryGridRowsChoice(),
            ))
            ->add('grid_rows_medium', 'choice', array(
                'label' => 'gallery.grid.rows.medium',
                'choices' => static::getGalleryGridRowsChoice(8),
            ))
            ->add('grid_rows_small', 'choice', array(
                'label' => 'gallery.grid.rows.small',
                'choices' => static::getGalleryGridRowsChoice(3),
            ))
        ;

        return $formBuilder->getForm();
    }

    /**
     * Test if gallery is in a slideshow mode.
     *
     * @return boolean
     */
    public function isSlidable()
    {
        return in_array($this->getGalleryMode(), array(
            'slideshow',
            'fullScreen',
        ));
    }

    /**
     * Test if gallery slides could have a link on current mode.
     *
     * @return boolean
     */
    public function isLinkable()
    {
        return in_array($this->getGalleryMode(), array(
            'vertical',
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
            'slideshow' => 'gallery.mode.slideshow',
            'fullScreen' => 'gallery.mode.fullScreen',
            'vertical' => 'gallery.mode.vertical',
            'masonry' => 'gallery.mode.masonry',
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
            'slide' => 'gallery.slide.slide',
            'fade' => 'gallery.slide.fade',
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
            $rows[(string)$row] = 'gallery.grid.rows'.$row;
        }
        return $rows;
    }

    /**
     * @return string
     */
    public function getGalleryMode()
    {
        return $this->galleryMode;
    }

    /**
     * @param string $galleryMode
     *
     * @return Gallery
     */
    public function setGalleryMode($galleryMode)
    {
        $this->galleryMode = $galleryMode;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlideMode()
    {
        return $this->slideMode;
    }

    /**
     * @param string $slideMode
     *
     * @return Gallery
     */
    public function setSlideMode($slideMode)
    {
        $this->slideMode = $slideMode;

        return $this;
    }

    /**
     * @return string
     */
    public function getExtended()
    {
        return $this->extended;
    }

    /**
     * @param string $extended
     *
     * @return Gallery
     */
    public function setExtended($extended)
    {
        $this->extended = $extended;

        return $this;
    }

    /**
     * @return string
     */
    public function getNavBar()
    {
        return $this->navBar;
    }

    /**
     * @param string $navBar
     *
     * @return Gallery
     */
    public function setNavBar($navBar)
    {
        $this->navBar = $navBar;

        return $this;
    }

    /**
     * @return string
     */
    public function getThumbList()
    {
        return $this->thumbList;
    }

    /**
     * @param string $thumbList
     *
     * @return Gallery
     */
    public function setThumbList($thumbList)
    {
        $this->thumbList = $thumbList;

        return $this;
    }

    /**
     * @return string
     */
    public function getGridRows()
    {
        return $this->gridRows;
    }

    /**
     * @param string $gridRows
     *
     * @return Gallery
     */
    public function setGridRows($gridRows)
    {
        $this->gridRows = $gridRows;

        return $this;
    }

    /**
     * @return string
     */
    public function getGridRowsMedium()
    {
        return $this->gridRowsMedium;
    }

    /**
     * @param string $gridRowsMedium
     *
     * @return Gallery
     */
    public function setGridRowsMedium($gridRowsMedium)
    {
        $this->gridRowsMedium = $gridRowsMedium;

        return $this;
    }

    /**
     * @return string
     */
    public function getGridRowsSmall()
    {
        return $this->gridRowsSmall;
    }

    /**
     * @param string $gridRowsSmall
     *
     * @return Gallery
     */
    public function setGridRowsSmall($gridRowsSmall)
    {
        $this->gridRowsSmall = $gridRowsSmall;

        return $this;
    }
}

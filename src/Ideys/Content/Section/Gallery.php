<?php

namespace Ideys\Content\Section;

use Ideys\Content\ContentInterface;
use Ideys\Settings\Settings;
use Symfony\Component\Form\FormFactory;

/**
 * Gallery content manager.
 */
class Gallery extends Section implements ContentInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getParameters()
    {
        return array(
            'gallery_mode' => 'slideshow',
            'slide_mode' => 'slide',
            'extended' => '0',
            'nav_bar' => '0',
            'thumb_list' => '0',
            'grid_rows' => '1',
            'grid_rows_medium' => '1',
            'grid_rows_small' => '1',
        );
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
            $weight += $slide->file_size;
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
        return in_array($this->getParameter('gallery_mode'), array(
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
        return in_array($this->getParameter('gallery_mode'), array(
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
            'full-screen' => 'gallery.mode.full.screen',
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
}

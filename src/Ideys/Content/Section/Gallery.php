<?php

namespace Ideys\Content\Section;

use Ideys\Content\Item\Slide;
use Ideys\Content\ContentInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Imagine\Image\ImagineInterface;

/**
 * Gallery content manager.
 */
class Gallery extends Section implements ContentInterface
{
    private $thumbSizes = array(1200, 220);

    /**
     * {@inheritdoc}
     */
    public static function getParameters()
    {
        return array(
            'gallery_mode' => 'slideshow',
            'thumb_list' => '0',
            'grid_rows' => '1',
        );
    }

    /**
     * Return the gallery directory path.
     *
     * @return string
     */
    public static function getGalleryDir()
    {
        return WEB_DIR.'/gallery';
    }

    /**
     * Add a slide into gallery.
     *
     * @param \Imagine\Image\ImagineInterface                       $imagine
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile   $file
     *
     * @return \Ideys\Content\Item\Slide
     */
    public function addSlide(ImagineInterface $imagine, UploadedFile $file)
    {
        $fileExt = $file->guessClientExtension();
        $realExt = $file->guessExtension();// from mime type
        $fileSize = $file->getClientSize();

        $slide = new Slide(array(
            'category' => $file->getMimeType(),
            'type' => \Ideys\Content\ContentFactory::ITEM_SLIDE,
        ));

        $slide->path = uniqid('expose').'.'.$fileExt;
        $slide->setParameter('real_ext', $realExt);
        $slide->setParameter('file_size', $fileSize);
        $slide->setParameter('original_name', $file->getClientOriginalName());

        $file->move(static::getGalleryDir(), $slide->path);

        foreach ($this->thumbSizes as $thumbSize){
            $this->createResizedSlide($imagine, $slide, $thumbSize);
        }

        return $slide;
    }

    /**
     * Create a resized slide file into dedicated directory.
     *
     * @param \Imagine\Image\ImagineInterface   $imagine
     * @param \Ideys\Content\Item\Slide         $slide
     * @param integer                           $maxWidth
     * @param integer                           $maxHeight
     *
     * @return \Ideys\Content\Item\Slide
     */
    public function createResizedSlide(ImagineInterface $imagine, Slide $slide, $maxWidth, $maxHeight = null)
    {
        $maxHeight = (null == $maxHeight) ? $maxWidth : $maxHeight;

        $thumbDir = static::getGalleryDir().'/'.$maxWidth;
        if (!is_dir($thumbDir)) {
            mkdir($thumbDir);
        }

        $transformation = new \Imagine\Filter\Transformation();
        $transformation->thumbnail(new \Imagine\Image\Box($maxWidth, $maxHeight))
            ->save($thumbDir.'/'.$slide->path);
        $transformation->apply($imagine
            ->open(static::getGalleryDir().'/'.$slide->path));

        return $slide;
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
            ->add('thumb_list', 'choice', array(
                'label' => 'gallery.thumb.list.display',
                'choices' => \Ideys\Settings\Settings::getIOChoices(),
            ))
            ->add('grid_rows', 'choice', array(
                'label' => 'gallery.grid.rows',
                'choices' => static::getGalleryGridRowsChoice(),
            ))
        ;

        return $formBuilder->getForm();
    }

    /**
     * Delete a selection of slides.
     *
     * @param array    $itemIds
     * @return array
     */
    public function deleteSlides($itemIds)
    {
        $deletedIds = array();

        foreach ($itemIds as $id) {
            if (is_numeric($id)
                && $this->deleteItemAndRelatedFile($this->items[$id])) {
                $deletedIds[] = $id;
            }
        }
        return $deletedIds;
    }

    /**
     * Delete the gallery from database
     * and remove this pictures.
     *
     * @return boolean
     */
    public function delete()
    {
        foreach ($this->items as $slide) {
            $this->deleteItemAndRelatedFile($slide);
        }

        return parent::delete();
    }

    /**
     * Delete item's data entry and related files.
     *
     * @param array $item
     * @return boolean
     */
    private function deleteItemAndRelatedFile(Slide $slide)
    {
        if (parent::deleteItem($slide->id)) {
            @unlink(WEB_DIR.'/gallery/'.$slide->path);
            foreach ($this->thumbSizes as $thumbSize){
                @unlink(WEB_DIR.'/gallery/'.$thumbSize.'/'.$slide->path);
            }
            return true;
        }
        return false;
    }

    /**
     * Test if gallery is in a slideshow mode
     *
     * @return boolean
     */
    public function isSlidable()
    {
        return in_array($this->getParameter('gallery_mode'), array(
            'slideshow',
            'fullscreen',
            'extended',
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
            'fullscreen' => 'gallery.mode.slideshow.full.screen',
            'extended' => 'gallery.mode.slideshow.extended',
            'horizontal' => 'gallery.mode.horizontal',
            'vertical' => 'gallery.mode.vertical',
            'masonry' => 'gallery.mode.masonry',
        );
    }

    /**
     * Return gallery grid rows choices.
     *
     * @return array
     */
    public static function getGalleryGridRowsChoice()
    {
        $rows = array();
        foreach (range(1, 12) as $row) {
            $rows[(string)$row] = 'gallery.grid.rows'.$row;
        }
        return $rows;
    }
}

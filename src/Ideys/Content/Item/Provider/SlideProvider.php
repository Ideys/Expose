<?php

namespace Ideys\Content\Item\Provider;

use Ideys\Content\Section\Entity\Section;
use Ideys\Content\Item\Entity\Item;
use Ideys\Content\Item\Entity\Slide;
use Ideys\Content\Section\Provider\GalleryProvider;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Box;
use Imagine\Filter\Transformation;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Slide item provider.
 */
class SlideProvider extends ItemProvider
{
    /**
     * Slides thumbs sizes.
     *
     * @var array
     */
    private $thumbSizes = array(1200, 220);

    /**
     * Add a slide into gallery.
     *
     * @param Section          $section
     * @param ImagineInterface $imagine
     * @param UploadedFile     $file
     *
     * @return Slide
     */
    public function addSlide(Section $section, ImagineInterface $imagine, UploadedFile $file)
    {
        $fileExt = $file->guessClientExtension();
        $realExt = $file->guessExtension();// from mime type
        $fileSize = $file->getClientSize();

        $slide = new Slide();
        $slide
            ->setCategory($file->getMimeType())
            ->setHierarchy($section->countItemsOfType(Item::ITEM_SLIDE) + 1)
            ->setPath(uniqid('expose').'.'.$fileExt)
            ->setRealExtension($realExt)
            ->setFileSize($fileSize)
            ->setOriginalName($file->getClientOriginalName());

        $file->move(GalleryProvider::getGalleryDir(), $slide->getPath());

        foreach ($this->thumbSizes as $thumbSize){
            $this->createResizeSlide($imagine, $slide, $thumbSize);
        }

        return $slide;
    }

    /**
     * Resize and save a slide file into dedicated directory.
     *
     * @param ImagineInterface $imagine
     * @param Slide            $slide
     * @param integer          $maxWidth
     * @param integer          $maxHeight
     *
     * @return Slide
     */
    public function createResizeSlide(ImagineInterface $imagine, Slide $slide, $maxWidth, $maxHeight = null)
    {
        $maxHeight = (null == $maxHeight) ? $maxWidth : $maxHeight;

        $thumbDir = GalleryProvider::getGalleryDir().'/'.$maxWidth;
        if (!is_dir($thumbDir)) {
            mkdir($thumbDir);
        }

        $transformation = new Transformation();
        $transformation->thumbnail(new Box($maxWidth, $maxHeight))
            ->save($thumbDir.'/'.$slide->getPath());
        $transformation->apply($imagine
            ->open(GalleryProvider::getGalleryDir().'/'.$slide->getPath()));

        return $slide;
    }

    /**
     * Delete a selection of slides.
     *
     * @param Section $section
     * @param array   $itemIds
     *
     * @return array
     */
    public function deleteSlides(Section $section, $itemIds)
    {
        $deletedIds = array();
        $items = $section->getItems();

        foreach ($items as $item) {
            if (in_array($item->getId(), $itemIds)
                && $item instanceof Slide
                && $this->deleteItemAndRelatedFile($item)) {
                $deletedIds[] = $item->getId();
            }
        }

        return $deletedIds;
    }

    /**
     * Delete item's data entry and related files.
     *
     * @param Slide $slide
     *
     * @return boolean
     */
    protected function deleteItemAndRelatedFile(Slide $slide)
    {
        if ($this->delete($slide)) {
            @unlink(WEB_DIR.'/gallery/'.$slide->getPath());
            foreach ($this->thumbSizes as $thumbSize){
                @unlink(WEB_DIR.'/gallery/'.$thumbSize.'/'.$slide->getPath());
            }
            return true;
        }

        return false;
    }
}

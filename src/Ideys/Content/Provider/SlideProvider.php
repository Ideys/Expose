<?php

namespace Ideys\Content\Provider;

use Ideys\Content\Item;
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

        $slide = new Item\Slide(array(
            'category' => $file->getMimeType(),
            'type' => Item\Item::ITEM_SLIDE,
            'hierarchy' => ($this->countItemsOfType(Item\Item::ITEM_SLIDE) + 1),
        ));

        $slide->setPath(uniqid('expose').'.'.$fileExt);
        $slide->addParameter('real_ext', $realExt);
        $slide->addParameter('file_size', $fileSize);
        $slide->addParameter('original_name', $file->getClientOriginalName());

        $file->move(GalleryProvider::getGalleryDir(), $slide->getPath());

        foreach ($this->thumbSizes as $thumbSize){
            $this->createResizeSlide($imagine, $slide, $thumbSize);
        }

        return $slide;
    }

    /**
     * Resize and save a slide file into dedicated directory.
     *
     * @param \Imagine\Image\ImagineInterface   $imagine
     * @param \Ideys\Content\Item\Slide         $slide
     * @param integer                           $maxWidth
     * @param integer                           $maxHeight
     *
     * @return \Ideys\Content\Item\Slide
     */
    public function createResizeSlide(ImagineInterface $imagine, Item\Slide $slide, $maxWidth, $maxHeight = null)
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
     * @param array    $itemIds
     *
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
     * Delete item's data entry and related files.
     *
     * @param \Ideys\Content\Item\Slide $slide
     *
     * @return boolean
     */
    protected function deleteItemAndRelatedFile(Item\Slide $slide)
    {
        if ($this->deleteItem($slide->getId())) {
            @unlink(WEB_DIR.'/gallery/'.$slide->getPath());
            foreach ($this->thumbSizes as $thumbSize){
                @unlink(WEB_DIR.'/gallery/'.$thumbSize.'/'.$slide->getPath());
            }
            return true;
        }
        return false;
    }
}

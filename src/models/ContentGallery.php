<?php

/**
 * Galleries content manager.
 */
class ContentGallery extends Content
{
    /**
     * Return default content form parameters.
     *
     * @return array
     */
    protected function getDefaultParameters()
    {
        return array(
            'gallery_mode' => 'slideshow',
        );
    }

    /**
     * Return a gallery section.
     *
     * @param integer $id
     * @return array
     */
    public function findSection($id)
    {
        $section = parent::findSection($id);

        $section['parameters'] = array_merge(
            $this->getDefaultParameters(),
            $section['parameters']
        );

        static::hydrateParameters($section);

        return $section;
    }

    /**
     * Return the form section edit form.
     *
     * @param array $section
     * @return \Symfony\Component\Form\Form
     */
    public function editForm($section)
    {
        $form = $this->sectionForm($section)
            ->remove('type')
            ->add('parameter_gallery_mode', 'choice', array(
                'label' => 'gallery.mode.mode',
                'choices' => static::getGalleryModeChoice(),
            ))
        ;

        return $form->getForm();
    }

    /**
     * Delete a selection of slides.
     *
     * @param integer  $sectionId
     * @param array    $itemIds
     * @return array
     */
    public function deleteSlides($sectionId, $itemIds)
    {
        $sectionItems = $this->findSectionItems($sectionId);
        $deletedIds = array();

        foreach ($itemIds as $id) {
            if (is_numeric($id)
                && $this->deleteItemAndRelatedFile($sectionItems[$id])) {
                $deletedIds[] = $id;
            }
        }
        return $deletedIds;
    }

    /**
     * Overwrite Content deleteSection method
     * to take into account pics deletion.
     *
     * @param integer $id
     */
    public function deleteSection($id)
    {
        $items = $this->findSectionItems($id);

        foreach ($items as $item) {
            $this->deleteItemAndRelatedFile($item);
        }

        return parent::deleteSection($id);
    }

    /**
     * Delete item's data entry and related files.
     *
     * @param array $item
     * @return boolean
     */
    private function deleteItemAndRelatedFile($item)
    {
        if (parent::deleteItem($item['id'])) {
            @unlink(WEB_DIR.'/gallery/'.$item['path']);
            @unlink(WEB_DIR.'/gallery/220/'.$item['path']);
            return true;
        }
        return false;
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
            'horizontal' => 'gallery.mode.horizontal',
            'vertical' => 'gallery.mode.vertical',
            'masonry' => 'gallery.mode.masonry',
        );
    }
}

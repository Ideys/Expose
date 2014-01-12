<?php

/**
 * Galleries content manager.
 */
class ContentGallery extends Content
{
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
}

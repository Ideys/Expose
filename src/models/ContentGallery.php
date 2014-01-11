<?php

/**
 * Galleries content manager.
 */
class ContentGallery extends Content
{
    public function deleteSlides($sectionId, $itemIds)
    {
        $sectionItems = $this->findSectionItems($sectionId);
        $deletedIds = array();

        foreach ($itemIds as $id) {
            if (parent::deleteItem($id)) {
                @unlink(WEB_DIR.'/gallery/'.$sectionItems[$id]['path']);
                @unlink(WEB_DIR.'/gallery/220/'.$sectionItems[$id]['path']);
                $deletedIds[] = $id;
            }
        }
        return $deletedIds;
    }
}

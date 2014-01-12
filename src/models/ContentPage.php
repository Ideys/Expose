<?php

/**
 * Pages content manager.
 */
class ContentPage extends Content
{
    /**
     * Add a first page to section,
     * using section data.
     *
     * @param integer $id
     * @return array
     */
    public function addFirstPage($id)
    {
        $section = $this->findSection($id);

        $this->addItem(
                $id,
                self::CONTENT_PAGE,
                null,
                $section['title'],
                $section['description'],
                null,
                array(),
                $this->language
        );

        return $section;
    }
}

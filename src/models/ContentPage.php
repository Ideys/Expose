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

        $this->addItem(array(
                'expose_section_id' => $id,
                'type' => self::CONTENT_PAGE,
                'title' => $section['title'],
                'description' => $section['description'],
            )
        );

        return $section;
    }
}

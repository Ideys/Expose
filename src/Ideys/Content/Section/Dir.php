<?php

namespace Ideys\Content\Section;

use Ideys\Content\ContentInterface;
use Ideys\Content\SectionInterface;

/**
 * Directory manager.
 */
class Dir extends Section implements ContentInterface, SectionInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getParameters()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultItemType()
    {
        return 'Section';
    }

    /**
     * Delete the directory with all
     * related sections.
     *
     * @return boolean
     */
    public function delete()
    {
        foreach ($this->getSections() as $section) {
            // Retrieve all section items,
            // to manage for example gallery pictures deletion.
            $section->hydrateItems();
            $section->delete();
        }

        return parent::delete();
    }
}

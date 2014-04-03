<?php

namespace Ideys\Content\Section;

use Ideys\Content\ContentInterface;

/**
 * Directory manager.
 */
class Dir extends Section implements ContentInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getParameters()
    {
        return array();
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

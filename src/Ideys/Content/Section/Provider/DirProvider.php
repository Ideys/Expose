<?php

namespace Ideys\Content\Section\Provider;

use Ideys\Content\Section\Entity\Section;

/**
 * Directory section provider.
 */
class DirProvider extends SectionProvider
{
    /**
     * Delete the directory with all
     * related sections.
     *
     * @param Section $section
     *
     * @return boolean
     */
    public function delete(Section $section)
    {
        foreach ($section->getSections() as $section) {
            // Retrieve all section items,
            // to manage for example gallery pictures deletion.
            $section->hydrateItems();
            $section->delete();
        }

        return parent::delete($section);
    }
}

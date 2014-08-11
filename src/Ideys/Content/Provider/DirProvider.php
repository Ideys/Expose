<?php

namespace Ideys\Content\Provider;

use Doctrine\DBAL\Connection;
use Ideys\Content\Item;

/**
 * Directory section provider.
 */
class DirProvider extends SectionProvider
{
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

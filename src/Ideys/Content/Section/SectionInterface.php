<?php

namespace Ideys\Content\Section;

/**
 * Sections interface.
 */
interface SectionInterface
{
    /**
     * Return id of Section.
     *
     * @return integer
     */
    public function getId();

    /**
     * Return default Section Items.
     *
     * @return array
     */
    public function getDefaultItems();

    /**
     * Test if the section could be composed with other sections items.
     *
     * @return boolean
     */
    public function isComposite();
}

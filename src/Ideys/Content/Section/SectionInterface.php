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
     * Return Section Items filtered by type.
     *
     * @param string $type
     *
     * @return array
     */
    public function getItemsOfType($type);

    /**
     * Test if the Section has some Items of specified type.
     *
     * @param string $type
     *
     * @return boolean
     */
    public function hasItemsOfType($type);

    /**
     * Count the number of Section main Items type.
     *
     * @return integer
     */
    public function countMainItems();

    /**
     * Test if the section could be composed with other sections items.
     *
     * @return boolean
     */
    public function isComposite();
}

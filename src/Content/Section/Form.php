<?php

namespace App\Content\Section;

use App\Content\Item\Field;
use App\Content\Item\Item;

/**
 * Form content manager.
 */
class Form extends Section implements SectionInterface
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->type = Section::SECTION_FORM;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultItems()
    {
        return $this->getFields();
    }

    /**
     * Get Field Items.
     *
     * @return Field[]
     */
    public function getFields()
    {
        return $this->getItemsOfType(Item::ITEM_FIELD);
    }

    /**
     * Test if Form has some Field Items.
     *
     * @return boolean
     */
    public function hasFields()
    {
        return $this->hasItemsOfType(Item::ITEM_FIELD);
    }

    /**
     * {@inheritdoc}
     */
    public function countMainItems()
    {
        return count($this->getFields());
    }

    /**
     * @return string
     */
    public function getValidationMessage()
    {
        return $this->retrieveParameter('validation_message');
    }

    /**
     * @param string $validationMessage
     *
     * @return Form
     */
    public function setValidationMessage($validationMessage)
    {
        $this->addParameter('validation_message', $validationMessage);

        return $this;
    }
}

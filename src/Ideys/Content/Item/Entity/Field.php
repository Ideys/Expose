<?php

namespace Ideys\Content\Item\Entity;

/**
 * Form Field class.
 */
class Field extends Item
{
    const TEXT     = 'text';
    const EMAIL    = 'email';
    const INTEGER  = 'integer';
    const TEXTAREA = 'textarea';
    const SELECT   = 'select';
    const MULTIPLE_SELECT = 'multiple.select';
    const CHECKBOX = 'checkbox';
    const RADIO    = 'radio';
    const FILE     = 'file';
    const HTML     = 'html.insert';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->type = self::ITEM_FIELD;
    }

    /**
     * Set required
     *
     * @param string $required
     *
     * @return Field
     */
    public function setRequired($required)
    {
        $this->addParameter('required', $required);

        return $this;
    }

    /**
     * Get required
     *
     * @return string
     */
    public function getRequired()
    {
        return $this->retrieveParameter('required', '0');
    }

    /**
     * Test if field is required.
     *
     * @return boolean
     */
    public function isRequired()
    {
        return ($this->getRequired() == '1');
    }

    /**
     * Set choices
     *
     * @param string $choices
     *
     * @return Field
     */
    public function setChoices($choices)
    {
        $this->addParameter('choices', $choices);

        return $this;
    }

    /**
     * Get choices
     *
     * @return string
     */
    public function getChoices()
    {
        return $this->retrieveParameter('choices');
    }

    /**
     * Return field types keys
     *
     * @return array
     */
    public static function getTypes()
    {
        return array(
            self::TEXT,
            self::EMAIL,
            self::INTEGER,
            self::TEXTAREA,
            self::SELECT,
            self::MULTIPLE_SELECT,
            self::CHECKBOX,
            self::RADIO,
            self::FILE,
            self::HTML,
        );
    }

    /**
     * Return field types keys and trans values
     *
     * @return array
     */
    public static function getTypesChoice()
    {
        $keys = static::getTypes();
        $values = array_map(function($item){
            return 'form.field.'.$item;
        }, $keys);

        return array_combine($keys, $values);
    }

    /**
     * Return Symfony form type equivalent.
     *
     * @param  string $type
     * @return string
     */
    public static function getSymfonyEquivalent($type)
    {
        $equivalents = array(
            self::TEXT     => 'text',
            self::EMAIL    => 'email',
            self::INTEGER  => 'integer',
            self::TEXTAREA => 'textarea',
            self::SELECT   => 'choice',
            self::MULTIPLE_SELECT => 'choice',
            self::CHECKBOX => 'checkbox',
            self::RADIO    => 'choice',
            self::FILE     => 'file',
        );

        return $equivalents[$type];
    }
}

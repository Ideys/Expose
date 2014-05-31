<?php

namespace Ideys\Content\Item;

use Ideys\Content\ContentInterface;

/**
 * Form Field class.
 */
class Field extends Item implements ContentInterface
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
     * {@inheritdoc}
     */
    public static function getParameters()
    {
        return array(
            'required' => '0',
            'choices' => '',
        );
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

<?php

use Doctrine\DBAL\Connection;
use Symfony\Component\Form\FormFactory;

/**
 * Dynamic forms manager.
 */
class DynamicForm
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $db;

    /**
     * @var \Symfony\Component\Form\FormFactory
     */
    private $formFactory;

    const TYPE_TEXT     = 'text';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_SELECT   = 'select';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_RADIO    = 'radio';

    /**
     * Constructor: inject required Silex dependencies.
     *
     * @param \Doctrine\DBAL\Connection             $connection
     * @param \Symfony\Component\Form\FormFactory   $formFactory
     */
    public function __construct(Connection  $connection,
                                FormFactory $formFactory)
    {
        $this->db = $connection;
        $this->formFactory = $formFactory;
        $this->language = 'fr';
    }

    /**
     * Return the form object with dynamic fields.
     *
     * @return \Symfony\Component\Form\Form
     */
    public function generateFormFields($items)
    {
        $form = $this->formFactory->createBuilder('form');

        foreach ($items as $item) {
            $form->add($item['slug'], static::typeEquivalent($item['type']), array(
                'label' => $item['title'],
            ));
        }

        return $form->getForm();
    }

    /**
     * Return fields types keys
     *
     * @return array
     */
    public static function getFieldTypes()
    {
        return array(
            self::TYPE_TEXT,
            self::TYPE_TEXTAREA,
            self::TYPE_SELECT,
            self::TYPE_CHECKBOX,
            self::TYPE_RADIO,
        );
    }

    /**
     * Return field types keys and trans values
     *
     * @return array
     */
    public static function getFieldTypesChoice()
    {
        $keys = static::getFieldTypes();
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
    public static function typeEquivalent($type)
    {
        $equivalents = array(
            self::TYPE_TEXT     => 'text',
            self::TYPE_TEXTAREA => 'textarea',
            self::TYPE_SELECT   => 'choice',
            self::TYPE_CHECKBOX => 'checkbox',
            self::TYPE_RADIO    => 'choice',
        );

        return $equivalents[$type];
    }
}

<?php

namespace Ideys\Content\Item\Type;

use Ideys\Content\Item\Entity\Field;
use Ideys\Settings\Settings;

/**
 * Form Field Item type.
 */
class FieldType extends ItemType
{
    /**
     * Return the item form builder.
     *
     * @param \Ideys\Content\Item\Entity\Field $field
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function formBuilder(Field $field)
    {
        $formBuilder = $this->formFactory
            ->createBuilder('form', $field)
            ->add('category', 'choice', array(
                'choices' => Field::getTypesChoice(),
                'label' => 'form.field.type',
            ))
            ->add('title', 'text', array(
                'label' => 'form.label',
                'attr' => array(
                    'placeholder' => 'form.label',
                ),
            ))
            ->add('required', 'choice', array(
                'label' => 'form.required',
                'choices' => Settings::getIOChoices(),
            ))
            ->add('description', 'textarea', array(
                'label' => 'form.help',
                'attr' => array(
                    'placeholder' => 'form.help',
                ),
                'required' => false,
            ))
            ->add('choices', 'textarea', array(
                'label' => 'form.choices',
                'attr' => array(
                    'placeholder' => 'form.choices',
                ),
                'required' => false,
            ))
        ;

        return $formBuilder;
    }
}

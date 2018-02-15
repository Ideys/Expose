<?php

namespace Ideys\Content\Item\Type;

use Ideys\Content\Item\Entity\Field;
use Ideys\Settings\Settings;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

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
            ->createBuilder(FormType::class, $field)
            ->add('category', ChoiceType::class, array(
                'choices' => Field::getTypesChoice(),
                'label' => 'form.field.type',
            ))
            ->add('title', TextType::class, array(
                'label' => 'form.label',
                'attr' => array(
                    'placeholder' => 'form.label',
                ),
            ))
            ->add('required', ChoiceType::class, array(
                'label' => 'form.required',
                'choices' => Settings::getIOChoices(),
            ))
            ->add('description', TextareaType::class, array(
                'label' => 'form.help',
                'attr' => array(
                    'placeholder' => 'form.help',
                ),
                'required' => false,
            ))
            ->add('choices', TextareaType::class, array(
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

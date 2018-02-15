<?php

namespace Ideys\Content\Section\Type;

use Ideys\Content\Section\Entity\Section;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Section Form type.
 */
class FormType extends SectionType
{
    /**
     * Return section form builder.
     *
     * @param \Ideys\Content\Section\Entity\Section $section
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function formBuilder(Section $section)
    {
        $formBuilder = parent::formBuilder($section)
            ->remove('type')
            ->add('validationMessage', TextareaType::class, array(
                'label' => 'form.validation.message',
                'required' => false,
            ))
        ;

        return $formBuilder;
    }
}

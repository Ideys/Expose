<?php

namespace Ideys\Content\Type;

use Ideys\Content\Section\Section;

/**
 * Section Form type.
 */
class SectionFormType extends SectionType
{
    /**
     * Return section form builder.
     *
     * @param \Ideys\Content\Section\Section $section
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function formBuilder(Section $section)
    {
        $formBuilder = parent::formBuilder($section)
            ->remove('type')
            ->add('validation_message', 'textarea', array(
                'label' => 'form.validation.message',
                'required' => false,
            ))
        ;

        return $formBuilder;
    }
}

<?php

namespace Ideys\Content\Type;

use Ideys\Content\Section\Section;

/**
 * Section Dir type.
 */
class SectionDirType extends SectionType
{
    /**
     * Return dir form builder.
     *
     * @param \Ideys\Content\Section\Section $section
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function formBuilder(Section $section)
    {
        $formBuilder = $this->formFactory
            ->createBuilder('form', $section)
            ->add('title', 'text', array(
                'label'         => 'section.title',
                'attr' => array(
                    'placeholder' => 'section.title',
                ),
            ))
        ;

        return $formBuilder;
    }
}

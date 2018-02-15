<?php

namespace Ideys\Content\Section\Type;

use Ideys\Content\Section\Entity\Section;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Section Dir type.
 */
class DirType extends SectionType
{
    /**
     * Return dir form builder.
     *
     * @param \Ideys\Content\Section\Entity\Section $section
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public function formBuilder(Section $section)
    {
        $formBuilder = $this->formFactory
            ->createBuilder(\Symfony\Component\Form\Extension\Core\Type\FormType::class, $section)
            ->add('title', TextType::class, array(
                'label'         => 'section.title',
                'attr' => array(
                    'placeholder' => 'section.title',
                ),
            ))
        ;

        return $formBuilder;
    }
}
